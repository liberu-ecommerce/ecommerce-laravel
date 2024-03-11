<?php

namespace Tests\Unit;

use App\Models\PaymentMethod;
use App\Services\PaymentGatewayService;
use Illuminate\Support\Facades\Config;
use PHPUnit\Framework\TestCase;
use Stripe\StripeClient;
use PayPal\Rest\ApiContext;
use PayPal\Auth\OAuthTokenCredential;

class PaymentGatewayServiceTest extends TestCase
{
    public function testProcessStripePayment()
    {
        // Mock PaymentMethod model and its findOrFail method
        $paymentMethod = $this->createMock(PaymentMethod::class);
        $paymentMethod->expects($this->once())
            ->method('findOrFail')
            ->willReturn($paymentMethod);

        // Mock StripeClient and its charges->create method
        $stripeClient = $this->createMock(StripeClient::class);
        $stripeClient->charges = $this->createMock(StripeClient\Charges::class);
        $stripeClient->charges->expects($this->once())
            ->method('create')
            ->willReturn((object) ['status' => 'succeeded']);

        // Create PaymentGatewayService instance
        $paymentGatewayService = new PaymentGatewayService();
        $paymentGatewayService->stripeClient = $stripeClient;

        // Call processStripePayment method
        $result = $paymentGatewayService->processStripePayment(1, 100);

        // Assert the expected behavior
        $this->assertEquals(['success' => true, 'data' => (object) ['status' => 'succeeded']], $result);
    }

    public function testProcessPaypalPayment()
    {
        // Mock PaymentMethod model and its findOrFail method
        $paymentMethod = $this->createMock(PaymentMethod::class);
        $paymentMethod->expects($this->once())
            ->method('findOrFail')
            ->willReturn($paymentMethod);

        // Mock PayPal payment processing logic
        $transactionStatus = 'success';

        // Create PaymentGatewayService instance
        $paymentGatewayService = new PaymentGatewayService();

        // Call processPaypalPayment method
        $result = $paymentGatewayService->processPaypalPayment(1, 100);

        // Assert the expected behavior
        $this->assertEquals(['success' => true, 'message' => 'PayPal payment successful'], $result);
    }
}
