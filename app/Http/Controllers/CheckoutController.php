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
        ]);

        $cart = $request->session()->get('cart', []);
        $shippingMethod = ShippingMethod::findOrFail($checkoutData['shipping_method_id']);

        $order = $this->createOrder($cart, $checkoutData, $shippingMethod);

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

    protected function calculateTotalAmount($cart, $shippingPrice)
    {
        $total = array_sum(array_map(function($item) {
            return $item['price'] * $item['quantity'];
        }, $cart));

        return $total + $shippingPrice;
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
            $product->inventory_count -= $item->quantity;
            $product->save();
        }

        // Clear the cart
        Session::forget('cart');
    }

    public function showConfirmation(Order $order)
    {
        return view('checkout.confirmation', compact('order'));
    }
}
