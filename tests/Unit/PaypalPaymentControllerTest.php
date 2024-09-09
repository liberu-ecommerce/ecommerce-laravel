<?php

namespace Tests\Unit;

use Tests\TestCase;
use Mockery;
use App\Http\Controllers\PaypalPaymentController;
use App\Services\PaymentGatewayService;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PaypalPaymentControllerTest extends TestCase
{
    public function testCreateOneTimePaymentSuccess()
    {
        $paymentGatewayServiceMock = Mockery::mock(PaymentGatewayService::class);
        $subscriptionServiceMock = Mockery::mock(SubscriptionService::class);
        $request = Request::create('/createOneTimePayment', 'POST', [
            'paymentMethodId' => 'validMethodId',
            'amount' => 100
        ]);

        $paymentGatewayServiceMock->shouldReceive('processPaypalPayment')
            ->once()
            ->with('validMethodId', 100)
            ->andReturn(['success' => true, 'message' => 'PayPal payment successful']);

        $controller = new PaypalPaymentController($paymentGatewayServiceMock, $subscriptionServiceMock);
        $response = $controller->createOneTimePayment($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->status());
        $this->assertEquals(['success' => true, 'message' => 'PayPal payment successful'], $response->getData(true));
    }

    public function testCreateOneTimePaymentFailure()
    {
        $paymentGatewayServiceMock = Mockery::mock(PaymentGatewayService::class);
        $subscriptionServiceMock = Mockery::mock(SubscriptionService::class);
        $request = Request::create('/createOneTimePayment', 'POST', [
            'paymentMethodId' => 'invalidMethodId',
            'amount' => 0
        ]);

        $paymentGatewayServiceMock->shouldReceive('processPaypalPayment')
            ->once()
            ->with('invalidMethodId', 0)
            ->andReturn(['success' => false, 'message' => 'Payment failed']);

        $controller = new PaypalPaymentController($paymentGatewayServiceMock, $subscriptionServiceMock);
        $response = $controller->createOneTimePayment($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->status());
        $this->assertEquals(['success' => false, 'message' => 'Payment failed'], $response->getData(true));
    }

    // Similar test methods will be created for createSubscription, updateSubscription, and cancelSubscription methods, covering all possible scenarios including success, failure, and edge cases.

}
