&lt;?php

namespace Tests\Feature;

use App\Http\Controllers\CheckoutController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class CheckoutControllerTest extends TestCase
{
    public function testGuestCheckout()
    {
        Session::shouldReceive('get')->once()->with('cart', [])->andReturn(['item1', 'item2']);
        Session::shouldReceive('put')->once()->with('guest_cart', ['item1', 'item2']);
        Session::shouldReceive('put')->once()->with('is_guest', true);

        $request = Request::create('/checkout/guest', 'POST');
        $controller = new CheckoutController();
        $controller->guestCheckout($request);
    }

    public function testCheckoutAsGuest()
    {
        Session::shouldReceive('get')->once()->with('is_guest', false)->andReturn(true);
        Session::shouldReceive('get')->once()->with('cart', [])->andReturn(['item1', 'item2']);
        Session::shouldReceive('put')->times(2);

        $request = Request::create('/checkout', 'POST');
        $controller = new CheckoutController();
        $controller->checkout($request);
    }

    public function testCheckoutAsRegisteredUser()
    {
        Session::shouldReceive('get')->once()->with('is_guest', false)->andReturn(false);

        $request = Request::create('/checkout', 'POST');
        $controller = new CheckoutController();
        $controller->checkout($request);
    }

    public function testVerifyPaymentAndShippingInfoValidData()
    {
        Validator::shouldReceive('make')->once()->andReturnSelf();
        Validator::shouldReceive('fails')->once()->andReturn(false);

        $controller = new CheckoutController();
        $controller->verifyPaymentAndShippingInfo([
            'email' => 'test@example.com',
            'shipping_address' => '123 Main St',
            'payment_method' => 'credit_card',
        ]);
    }

    public function testVerifyPaymentAndShippingInfoInvalidData()
    {
        $this->expectException(ValidationException::class);

        Validator::shouldReceive('make')->once()->andReturnSelf();
        Validator::shouldReceive('fails')->once()->andReturn(true);

        $controller = new CheckoutController();
        $controller->verifyPaymentAndShippingInfo([
            'email' => 'invalid',
            'shipping_address' => '',
            'payment_method' => '',
        ]);
    }
}
