<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Models\ShippingMethod;
use App\Services\ShippingService;
use App\Models\Order;
use App\Models\Coupon;
use App\Models\InventoryLog;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use App\Factories\PaymentGatewayFactory;
use App\Models\Product;
use App\Notifications\OrderConfirmationNotification;
use App\Services\TaxService;
use App\Services\CouponService;
use Exception;

class CheckoutController extends Controller
{
    /** Sentinel thrown inside the reservation transaction to roll it back on insufficient stock. */
    private const OUT_OF_STOCK = 'OUT_OF_STOCK';

    protected $shippingService;
    protected $taxService;
    protected $couponService;

    public function __construct(ShippingService $shippingService, TaxService $taxService, CouponService $couponService)
    {
        $this->shippingService = $shippingService;
        $this->taxService = $taxService;
        $this->couponService = $couponService;
    }

    public function initiateCheckout(Request $request)
    {
        $isGuest = Session::get('is_guest', false);
        $cart = Session::get('cart', []);

        if (empty($cart)) {
            return redirect()->route('products.index')
                ->with('error', 'Your cart is empty');
        }

        $shippingMethods = $this->shippingService->getAvailableShippingMethods();

        // Calculate subtotal and whether physical products exist
        $subtotal = collect($cart)->sum(function ($item) {
            return $item['price'] * $item['quantity'];
        });

        $hasPhysicalProducts = $this->hasPhysicalProducts($cart);

        return view('checkout.checkout', [
            'cart' => $cart,
            'shippingMethods' => $shippingMethods,
            'isGuest' => $isGuest,
            'subtotal' => $subtotal,
            'total' => $subtotal,
            'hasPhysicalProducts' => $hasPhysicalProducts,
        ]);
    }

    public function processCheckout(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'has_physical_products' => 'sometimes|in:0,1',
            'shipping_address' => 'required_if:has_physical_products,1|string',
            'shipping_method_id' => 'required_if:has_physical_products,1|exists:shipping_methods,id',
            'payment_method' => 'required|string',
            'recipient_name' => 'required_if:dropship,on|string',
            'recipient_email' => 'required_if:dropship,on|email',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $cart = Session::get('cart', []);
        
        if (empty($cart)) {
            return redirect()->route('products.index')
                ->with('error', 'Your cart is empty');
        }

        // Verify inventory before processing
        foreach ($cart as $productId => $item) {
            $product = Product::find($productId);
            if (!$product || $product->inventory_count < $item['quantity']) {
                return redirect()->back()->with('error', 'Some items in your cart are no longer available in the requested quantity.');
            }
        }

        // Calculate total amount
        $subtotal = collect($cart)->sum(function($item) {
            return $item['price'] * $item['quantity'];
        });

        $shippingCost = 0;
        if ($this->hasPhysicalProducts($cart)) {
            $shippingMethod = ShippingMethod::find($request->shipping_method_id);
            $shippingCost = $request->has('dropship') ?
                $this->shippingService->calculateDropShippingCost($shippingMethod, $cart, $request->shipping_address) :
                $this->shippingService->calculateShippingCost($shippingMethod, $cart, $request->shipping_address) ?? $shippingMethod->base_rate;
        }

        // Apply coupon discount if available
        $discountAmount = 0;
        $couponId = null;
        $couponCode = null;
        $couponData = Session::get('coupon');
        if ($couponData) {
            $discountAmount = $couponData['discount'] ?? 0;
            $couponId = $couponData['coupon_id'] ?? null;
            $couponCode = $couponData['code'] ?? null;
        }

        // Calculate tax on the amount after discount.
        $taxAmount = $this->taxService->calculateTaxForCart($cart, $request->shipping_address, $discountAmount);

        $totalAmount = $subtotal - $discountAmount + $shippingCost + $taxAmount;

        // Create the order and RESERVE stock atomically, before charging.
        // If any line can't be reserved (e.g. a concurrent buyer took the last
        // unit), the whole transaction rolls back and no payment is taken — this
        // is what prevents charging a customer for stock we can't fulfil.
        $order = null;
        try {
            DB::transaction(function () use (&$order, $cart, $request, $totalAmount, $shippingCost, $taxAmount, $discountAmount, $couponCode) {
                $order = Order::create([
                    'user_id' => auth()->id(),
                    'customer_email' => $request->email,
                    'shipping_address' => $request->shipping_address,
                    'shipping_method_id' => $request->shipping_method_id,
                    'payment_method' => $request->payment_method,
                    'total_amount' => $totalAmount,
                    'shipping_cost' => $shippingCost,
                    'tax_amount' => $taxAmount,
                    'discount_amount' => $discountAmount,
                    'coupon_code' => $couponCode,
                    'status' => 'pending',
                    'is_dropshipped' => $request->has('dropship'),
                    'recipient_name' => $request->recipient_name,
                    'recipient_email' => $request->recipient_email,
                    'gift_message' => $request->gift_message,
                ]);

                foreach ($cart as $productId => $item) {
                    $order->items()->create([
                        'product_id' => $productId,
                        'quantity' => $item['quantity'],
                        'price' => $item['price'],
                    ]);

                    $before = Product::where('id', $productId)->value('inventory_count');

                    // Atomic, guarded decrement: only succeeds while enough stock remains.
                    $affected = Product::where('id', $productId)
                        ->where('inventory_count', '>=', $item['quantity'])
                        ->decrement('inventory_count', $item['quantity']);

                    if ($affected === 0) {
                        // Not enough stock (or product gone) — abort the whole order.
                        throw new \RuntimeException(self::OUT_OF_STOCK);
                    }

                    InventoryLog::create([
                        'product_id' => $productId,
                        'quantity_change' => -$item['quantity'],
                        'old_quantity' => $before,
                        'new_quantity' => $before - $item['quantity'],
                        'reason' => 'order',
                        'reference_id' => $order->id,
                        'reference_type' => Order::class,
                    ]);
                }
            });
        } catch (\RuntimeException $e) {
            if ($e->getMessage() === self::OUT_OF_STOCK) {
                return redirect()->back()
                    ->with('error', 'Some items in your cart are no longer available in the requested quantity.');
            }
            throw $e;
        }

        /** @var Order $order */

        // Charge payment. Stock is already reserved, so we never charge for stock
        // we can't fulfil. If the charge fails, release the reservation.
        if ($totalAmount > 0) {
            if ($request->payment_method === 'stripe' && $request->has('stripeToken')) {
                $paymentResult = $this->processStripePayment($order, $request->stripeToken);
            } else if ($request->payment_method === 'paypal' && $request->has('paypal_payment_id')) {
                $paymentResult = $this->processPayPalPayment($order, $request->paypal_payment_id);
            } else {
                $this->releaseInventory($order, $cart);
                $order->transitionTo(Order::STATUS_FAILED, notes: 'Invalid payment information');
                return redirect()->back()
                    ->with('error', 'Invalid payment information. Please try again.');
            }

            if (!$paymentResult['success']) {
                $this->releaseInventory($order, $cart);
                $order->transitionTo(Order::STATUS_FAILED, notes: 'Payment failed: ' . ($paymentResult['error'] ?? 'unknown'));
                return redirect()->back()
                    ->with('error', 'Payment failed: ' . ($paymentResult['error'] ?? 'Please try again.'));
            }
        }

        // Persist the gateway charge id so a later refund has something to void.
        if (isset($paymentResult['transaction_id'])) {
            $order->update(['transaction_id' => $paymentResult['transaction_id']]);
        }

        $order->transitionTo(Order::STATUS_PAID, notes: 'Payment captured');

        // Send order confirmation email
        Notification::route('mail', $order->customer_email)
            ->notify(new OrderConfirmationNotification($order));

        // If dropshipping, queue supplier order placement
        if ($order->is_dropshipped) {
            try {
                $supplierId = $request->input('supplier_id', 'dropxl');
                // persist chosen supplier so admin can see it immediately
                $order->update(['supplier_id' => $supplierId]);

                // dispatch a job to place the supplier order asynchronously
                \App\Jobs\DispatchDropshippingOrder::dispatch($order->id, $supplierId);

                // set temporary status indicating background placement
                $order->transitionTo(Order::STATUS_SUPPLIER_QUEUED, notes: "Supplier order queued ({$supplierId})");

            } catch (Exception $e) {
                \Log::error('Dropshipping dispatch error: ' . $e->getMessage());
                $order->transitionTo(Order::STATUS_SUPPLIER_FAILED, notes: 'Dropshipping dispatch error: ' . $e->getMessage());

                Notification::route('mail', config('mail.from.address'))
                    ->notify(new \App\Notifications\SupplierFailureNotification("Error queuing dropshipping order for order {$order->id}: " . $e->getMessage()));

                return redirect()->route('checkout.confirmation', ['order' => $order->id])
                    ->with('warning', 'Order placed but an error occurred while queuing the supplier order. Our team will follow up.');
            }
        }

        // Generate download links for downloadable products
        foreach ($cart as $productId => $item) {
            if ($item['is_downloadable']) {
                $product = Product::with('category')->find($productId);
                $orderItem = $order->items()->where('product_id', $productId)->first();

                if ($orderItem && $product) {
                    // Generate secure download link with expiration (30 days)
                    $token = Str::random(64);
                    $categoryId = $product->category ? $product->category->id : 'general';
                    $downloadLink = route('download.serve-file', [
                        'category' => $categoryId,
                        'product' => $product->id,
                        'token' => $token
                    ]);
                    
                    $orderItem->update([
                        'download_link' => $token, // Store token, not full URL
                        'download_expires_at' => now()->addDays(30),
                        'download_count' => 0,
                    ]);
                }
            }
        }

        // Clear cart and coupon
        Session::forget('cart');
        Session::forget('coupon');
        
        return redirect()->route('checkout.confirmation', ['order' => $order->id])
            ->with('success', 'Order placed successfully!');
    }

    public function showConfirmation(Order $order)
    {
        return view('checkout.confirmation', [
            'order' => $order
        ]);
    }

    public function guestCheckout(Request $request)
    {
        $cart = Session::get('cart', []);
        
        // Store cart in guest session
        Session::put('guest_cart', $cart);
        Session::put('is_guest', true);

        return redirect()->route('checkout.initiate');
    }

    protected function processPayment($order, $paymentMethod)
    {
        $paymentGateway = PaymentGatewayFactory::create($paymentMethod);
        return $paymentGateway->processPayment($order->total_amount, [
            'order_id' => $order->id,
            'customer_email' => $order->customer_email
        ]);
    }

    /**
     * Return reserved stock to inventory when a payment fails after the order was
     * created. Keeps an audit row so the reserve/release pair is traceable.
     */
    private function releaseInventory($order, array $cart): void
    {
        foreach ($cart as $productId => $item) {
            $before = Product::where('id', $productId)->value('inventory_count');
            if ($before === null) {
                continue;
            }

            Product::where('id', $productId)->increment('inventory_count', $item['quantity']);

            InventoryLog::create([
                'product_id' => $productId,
                'quantity_change' => $item['quantity'],
                'old_quantity' => $before,
                'new_quantity' => $before + $item['quantity'],
                'reason' => 'payment_failed_release',
                'reference_id' => $order->id,
                'reference_type' => Order::class,
            ]);
        }
    }

    private function hasPhysicalProducts($cart)
    {
        foreach ($cart as $item) {
            if (!$item['is_downloadable']) {
                return true;
            }
        }
        return false;
    }

    protected function processStripePayment($order, $stripeToken)
    {
        $paymentGateway = PaymentGatewayFactory::create('stripe');
        return $paymentGateway->processPayment($order->total_amount, [
            'order_id' => $order->id,
            'customer_email' => $order->customer_email,
            'token' => $stripeToken
        ]);
    }

    protected function processPayPalPayment($order, $paypalPaymentId)
    {
        $paymentGateway = PaymentGatewayFactory::create('paypal');
        return $paymentGateway->processPayment($order->total_amount, [
            'order_id' => $order->id,
            'customer_email' => $order->customer_email,
            'payment_id' => $paypalPaymentId
        ]);
    }
}
