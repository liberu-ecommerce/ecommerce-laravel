&lt;?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class CheckoutController extends Controller
{
    public function guestCheckout(Request $request)
    {
        $cart = $request->session()->get('cart', []);
        $request->session()->put('guest_cart', $cart);
        $request->session()->put('is_guest', true);
    }

    public function checkout(Request $request)
    {
        if ($request->session()->get('is_guest', false)) {
            $this->guestCheckout($request);
        }

        // Streamline checkout steps
        $checkoutData = $request->only(['email', 'shipping_address', 'payment_method']);
        $this->verifyPaymentAndShippingInfo($checkoutData);

        // Proceed with checkout logic...
    }

    protected function verifyPaymentAndShippingInfo(array $data)
    {
        $validator = Validator::make($data, [
            'email' => 'required|email',
            'shipping_address' => 'required|string|max:255',
            'payment_method' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            throw new \Illuminate\Validation\ValidationException($validator);
        }

        // Verification logic...
    }
}
