<?php

namespace Tests\Feature;

use App\Interfaces\PaymentGatewayInterface;
use App\Services\PaymentGatewayService;
use App\Services\PaymentGateways\PayPalGateway;
use App\Services\PaymentGateways\StripeGateway;
use Tests\TestCase;

class PaymentGatewayServiceTest extends TestCase
{
    private function fakeGateway(array $return): PaymentGatewayInterface
    {
        return new class($return) implements PaymentGatewayInterface {
            public function __construct(private array $return) {}

            public function processPayment(float $amount, array $paymentDetails): array
            {
                return $this->return + ['amount' => $amount];
            }

            public function processSubscription(string $planId, array $subscriptionDetails): array
            {
                return $this->return;
            }

            public function refundPayment(string $transactionId, float $amount): array
            {
                return $this->return + ['amount' => $amount];
            }
        };
    }

    public function test_process_payment_delegates_to_the_named_gateway(): void
    {
        $this->app->instance(StripeGateway::class, $this->fakeGateway(['success' => true, 'gateway' => 'stripe']));

        $result = app(PaymentGatewayService::class)->processPayment('stripe', 10.0, ['token' => 'tok']);

        $this->assertTrue($result['success']);
        $this->assertSame('stripe', $result['gateway']);
        $this->assertSame(10.0, $result['amount']);
    }

    public function test_refund_payment_delegates_to_the_named_gateway(): void
    {
        $this->app->instance(PayPalGateway::class, $this->fakeGateway(['success' => true, 'gateway' => 'paypal']));

        $result = app(PaymentGatewayService::class)->refundPayment('paypal', 'txn_1', 5.0);

        $this->assertTrue($result['success']);
        $this->assertSame('paypal', $result['gateway']);
        $this->assertSame(5.0, $result['amount']);
    }
}
