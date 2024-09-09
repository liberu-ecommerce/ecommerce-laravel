<?php

namespace Tests\Feature;

use App\Http\Controllers\CheckoutController;
use App\Models\Order;
use App\Models\ShippingMethod;
use App\Services\ShippingService;
use App\Services\PaymentGatewayService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class CheckoutControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $shippingService;
    protected $paymentGatewayService;

    public function setUp(): void
    {
        parent::setUp();
        $this->shippingService = $this->createMock(ShippingService::class);
        $this->paymentGatewayService = $this->createMock(PaymentGatewayService::class);
    }

    public function testGuestCheckout()
    {
        Session::shouldReceive('get')->once()->with('cart', [])->andReturn(['item1', 'item2']);
        Session::shouldReceive('put')->once()->with('guest_cart', ['item1', 'item2']);
        Session::shouldReceive('put')->once()->with('is_guest', true);

        $request = Request::create('/checkout/guest', 'POST');
        $controller = new CheckoutController($this->shippingService, $this->paymentGatewayService);
        $controller->guestCheckout($request);
    }

    public function testInitiateCheckout()
    {
        Session::shouldReceive('get')->once()->with('is_guest', false)->andReturn(false);
        Session::shouldReceive('get')->once()->with('cart', [])->andReturn(['item1', 'item2']);

        $this->shippingService->expects($this->once())
            ->method('getAvailableShippingMethods')
            ->willReturn([new ShippingMethod(['id' => 1, 'name' => 'Standard Shipping', 'price' => 5.00])]);

        $request = Request::create('/checkout', 'GET');
        $controller = new CheckoutController($this->shippingService, $this->paymentGatewayService);

        $response = $controller->initiateCheckout($request);

        $this->assertEquals('checkout.checkout', $response->name());
        $this->assertArrayHasKey('cart', $response->getData());
        $this->assertArrayHasKey('shippingMethods', $response->getData());
    }

    public function testProcessCheckout()
    {
        $shippingMethod = ShippingMethod::factory()->create();
        $cart = [
            ['product_id' => 1, 'quantity' => 2, 'price' => 10.00],
            ['product_id' => 2, 'quantity' => 1, 'price' => 15.00],
        ];

        Session::shouldReceive('get')->once()->with('cart', [])->andReturn($cart);
        Session::shouldReceive('forget')->once()->with('cart');

        $this->paymentGatewayService->expects($this->once())
            ->method('processPayment')
            ->willReturn(['success' => true]);

        $request = Request::create('/checkout/process', 'POST', [
            'email' => 'test@example.com',
            'shipping_address' => '123 Test St',
            'shipping_method_id' => $shippingMethod->id,
            'payment_method' => 'credit_card',
        ]);

        $controller = new CheckoutController($this->shippingService, $this->paymentGatewayService);

        $response = $controller->processCheckout($request);

        $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $response);
        $this->assertEquals(route('checkout.confirmation', ['order' => 1]), $response->getTargetUrl());

        $this->assertDatabaseHas('orders', [
            'customer_email' => 'test@example.com',
            'shipping_address' => '123 Test St',
            'shipping_method_id' => $shippingMethod->id,
            'payment_method' => 'credit_card',
            'status' => 'paid',
        ]);
    }

    public function testShowConfirmation()
    {
        $order = Order::factory()->create();

        $controller = new CheckoutController($this->shippingService, $this->paymentGatewayService);

        $response = $controller->showConfirmation($order);

        $this->assertEquals('checkout.confirmation', $response->name());
        $this->assertEquals($order->id, $response->getData()['order']->id);
    }
}
