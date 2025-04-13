<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use App\Models\ShippingMethod;
use App\Services\ShippingService;
use App\Services\PaymentGatewayService;
use App\Models\Order;
use App\Models\Coupon;
use App\Models\InventoryLog;
use App\Models\User;
use App\Notifications\LowStockNotification;
use Illuminate\Support\Facades\Notification;
use App\Factories\PaymentGatewayFactory;
use App\Models\Product; // Add this line to import the Product model

class CheckoutController extends Controller
{
    protected $shippingService;

    public function __construct(ShippingService $shippingService)
    {
        $this->shippingService = $shippingService;
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

        return view('checkout.checkout', [
            'cart' => $cart,
            'shippingMethods' => $shippingMethods,
            'isGuest' => $isGuest
        ]);
    }

    public function processCheckout(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'shipping_address' => 'required_if:has_physical_products,true|string',
            'shipping_method_id' => 'required_if:has_physical_products,true|exists:shipping_methods,id',
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
                $shippingMethod->base_rate;
        }

        $totalAmount = $subtotal + $shippingCost;

        // Create order
        $order = Order::create([
            'customer_email' => $request->email,
            'shipping_address' => $request->shipping_address,
            'shipping_method_id' => $request->shipping_method_id,
            'payment_method' => $request->payment_method,
            'total_amount' => $totalAmount,
            'status' => 'pending',
            'is_dropshipping' => $request->has('dropship'),
            'recipient_name' => $request->recipient_name,
            'recipient_email' => $request->recipient_email,
            'gift_message' => $request->gift_message,
        ]);

        // Process payment based on selected method
        if ($totalAmount > 0) {
            if ($request->payment_method === 'stripe' && $request->has('stripeToken')) {
                $paymentResult = $this->processStripePayment($order, $request->stripeToken);
            } else if ($request->payment_method === 'paypal' && $request->has('paypal_payment_id')) {
                $paymentResult = $this->processPayPalPayment($order, $request->paypal_payment_id);
            } else {
                $order->update(['status' => 'failed']);
                return redirect()->back()
                    ->with('error', 'Invalid payment information. Please try again.');
            }

            if (!$paymentResult['success']) {
                $order->update(['status' => 'failed']);
                return redirect()->back()
                    ->with('error', 'Payment failed: ' . ($paymentResult['error'] ?? 'Please try again.'));
            }
        }

        $order->update(['status' => 'paid']);

        // Generate download links for downloadable products
        foreach ($cart as $productId => $item) {
            if ($item['is_downloadable']) {
                $downloadLink = route('download.generate-link', $productId);
                // Store download link in order items or send via email
            }
        }
        
        // Update inventory after successful payment
        foreach ($cart as $productId => $item) {
            $product = Product::find($productId);
            $product->decrement('inventory_count', $item['quantity']);
        }

        // Clear cart
        Session::forget('cart');
        
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