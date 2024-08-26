&lt;?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use App\Models\ShippingMethod;
use App\Services\ShippingService;
use App\Services\PaymentGatewayService;
use App\Models\Order;

class CheckoutController extends Controller
{
    protected $shippingService;
    protected $paymentGatewayService;

    public function __construct(ShippingService $shippingService, PaymentGatewayService $paymentGatewayService)
    {
        $this->shippingService = $shippingService;
        $this->paymentGatewayService = $paymentGatewayService;
    }

    public function guestCheckout(Request $request)
    {
        $cart = $request->session()->get('cart', []);
        $request->session()->put('guest_cart', $cart);
        $request->session()->put('is_guest', true);
    }

    public function initiateCheckout(Request $request)
    {
        if ($request->session()->get('is_guest', false)) {
            $this->guestCheckout($request);
        }

        $cart = $request->session()->get('cart', []);
        $shippingMethods = $this->shippingService->getAvailableShippingMethods($cart);

        return view('checkout.checkout', compact('cart', 'shippingMethods'));
    }

    public function processCheckout(Request $request)
    {
        $checkoutData = $request->validate([
            'email' => 'required|email',
            'shipping_address' => 'required|string|max:255',
            'shipping_method_id' => 'required|exists:shipping_methods,id',
            'payment_method' => 'required|string|max:255',
            'coupon_code' => 'nullable|string|max:255',
        ]);
    
        $cart = $request->session()->get('cart', []);
        $shippingMethod = ShippingMethod::findOrFail($checkoutData['shipping_method_id']);
    
        $coupon = null;
        if (!empty($checkoutData['coupon_code'])) {
            $coupon = $this->validateCoupon($checkoutData['coupon_code']);
            if (!$coupon) {
                return back()->withErrors(['coupon' => 'Invalid coupon code.']);
            }
        }
    
        $order = $this->createOrder($cart, $checkoutData, $shippingMethod, $coupon);
    
        $paymentResult = $this->processPayment($order, $checkoutData['payment_method']);
    
        if ($paymentResult['success']) {
            $this->finalizeOrder($order);
            return redirect()->route('checkout.confirmation', ['order' => $order->id]);
        } else {
            return back()->withErrors(['payment' => 'Payment failed. Please try again.']);
        }
    }

    protected function createOrder($cart, $checkoutData, $shippingMethod)
    {
        $order = new Order();
        $order->customer_email = $checkoutData['email'];
        $order->shipping_address = $checkoutData['shipping_address'];
        $order->shipping_method_id = $shippingMethod->id;
        $order->total_amount = $this->calculateTotalAmount($cart, $shippingMethod->price);
        $order->save();

        foreach ($cart as $item) {
            $order->items()->create([
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'price' => $item['price'],
            ]);
        }

        return $order;
    }

    protected function calculateTotalAmount($cart, $shippingPrice, $coupon = null)
    {
        $total = array_sum(array_map(function($item) {
            return $item['price'] * $item['quantity'];
        }, $cart));

        // Apply bulk purchase discount
        $totalItems = array_sum(array_column($cart, 'quantity'));
        if ($totalItems >= 10) {
            $bulkDiscount = $total * 0.05; // 5% discount for 10 or more items
            $total -= $bulkDiscount;
        }

        $subtotal = $total + $shippingPrice;

        // Apply coupon discount
        if ($coupon && $coupon->isValid() && $subtotal >= $coupon->min_purchase_amount) {
            if ($coupon->type === 'percentage') {
                $discount = $subtotal * ($coupon->value / 100);
            } else {
                $discount = $coupon->value;
            }
            $subtotal -= $discount;
        }

        return max($subtotal, 0);
    }
    
    protected function validateCoupon($code)
    {
        $coupon = Coupon::where('code', $code)->first();
    
        if (!$coupon || !$coupon->isValid()) {
            return null;
        }
    
        return $coupon;
    }

    protected function processPayment($order, $paymentMethod)
    {
        return $this->paymentGatewayService->processPayment($paymentMethod, $order->total_amount);
    }

    protected function finalizeOrder($order)
    {
        $order->status = 'paid';
        $order->save();

        // Update inventory
        foreach ($order->items as $item) {
            $product = $item->product;
            $oldInventory = $product->inventory_count;
            $product->inventory_count -= $item->quantity;
            $product->save();

            // Create InventoryLog entry
            InventoryLog::create([
                'product_id' => $product->id,
                'quantity_change' => -$item->quantity,
                'reason' => 'Order #' . $order->id,
            ]);

            // Check for low stock
            if ($product->isLowStock()) {
                $admins = User::where('is_admin', true)->get();
                Notification::send($admins, new LowStockNotification($product));
            }
        }

        // Clear the cart
        Session::forget('cart');
    }

    public function showConfirmation(Order $order)
    {
        return view('checkout.confirmation', compact('order'));
    }
}
