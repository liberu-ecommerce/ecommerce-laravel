<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\PaymentGatewayService;
use App\Models\PaymentMethod;
use Illuminate\Support\Facades\Config;
use Mockery;

class PaymentGatewayServiceTest extends TestCase
{
    public function testProcessPaypalPaymentSuccess()
    {
        Config::shouldReceive('get')
            ->with('services.paypal.client_id')
            ->andReturn('dummy_client_id');
        Config::shouldReceive('get')
            ->with('services.paypal.secret')
            ->andReturn('dummy_secret');
        Config::shouldReceive('get')
            ->with('services.paypal.settings')
            ->andReturn(['mode' => 'sandbox']);

        $paymentMethod = new PaymentMethod(['id' => 'validMethodId', 'details' => 'validDetails']);
        PaymentMethod::shouldReceive('findOrFail')
            ->with('validMethodId')
            ->andReturn($paymentMethod);

        $service = new PaymentGatewayService();
        $response = $service->processPaypalPayment('validMethodId', 100);

        $this->assertTrue($response['success']);
        $this->assertEquals('PayPal payment successful', $response['message']);
    }

    public function testProcessPaypalPaymentFailure()
    {
        PaymentMethod::shouldReceive('findOrFail')
            ->with('invalidMethodId')
            ->andThrow(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        $service = new PaymentGatewayService();
        $response = $service->processPaypalPayment('invalidMethodId', 100);

        $this->assertFalse($response['success']);
        $this->assertEquals('PayPal payment failed', $response['error']);
    }

    public function testProcessPaypalSubscriptionSuccess()
    {
        Config::shouldReceive('get')
            ->with('services.paypal.client_id')
            ->andReturn('dummy_client_id');
        Config::shouldReceive('get')
            ->with('services.paypal.secret')
            ->andReturn('dummy_secret');
        Config::shouldReceive('get')
            ->with('services.paypal.settings')
            ->andReturn(['mode' => 'sandbox']);

        $service = new PaymentGatewayService();
        $response = $service->processPaypalSubscription('validMethodId', 'validPlanId');

        $this->assertTrue($response['success']);
        $this->assertNotEmpty($response['agreementID']);
    }

    public function testProcessPaypalSubscriptionFailure()
    {
        Config::shouldReceive('get')
            ->with('services.paypal.client_id')
            ->andReturn('dummy_client_id');
        Config::shouldReceive('get')
            ->with('services.paypal.secret')
            ->andReturn('dummy_secret');
        Config::shouldReceive('get')
            ->with('services.paypal.settings')
            ->andReturn(['mode' => 'sandbox']);

        $service = new PaymentGatewayService();
        $response = $service->processPaypalSubscription('invalidMethodId', 'invalidPlanId');

        $this->assertFalse($response['success']);
        $this->assertNotEmpty($response['error']);
    }
}
