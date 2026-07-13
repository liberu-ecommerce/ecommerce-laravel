<?php

namespace Tests\Feature;

use App\Interfaces\PaymentGatewayInterface;
use App\Models\User;
use App\Services\PaymentGateways\PayPalGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaypalPaymentControllerTest extends TestCase
{
    use RefreshDatabase;

    private function bindPaypalGateway(array $return): void
    {
        $this->app->instance(PayPalGateway::class, new class($return) implements PaymentGatewayInterface
        {
            public function __construct(private array $return) {}

            public function processPayment(float $amount, array $paymentDetails): array
            {
                return $this->return + ['payment_id' => $paymentDetails['payment_id'] ?? null];
            }

            public function processSubscription(string $planId, array $subscriptionDetails): array
            {
                return $this->return;
            }

            public function refundPayment(string $transactionId, float $amount): array
            {
                return $this->return;
            }
        });
    }

    public function test_one_time_payment_requires_authentication(): void
    {
        // The route captures a caller-supplied PayPal order id against the merchant's
        // live PayPal credentials — an anonymous request must not reach it (matches the
        // sibling /paypal/subscription routes, already behind auth).
        $this->postJson(route('paypal.payment.create'), [
            'paymentMethodId' => 'pm_123',
            'amount' => 25,
        ])->assertUnauthorized();
    }

    public function test_one_time_payment_requires_a_payment_method_id(): void
    {
        $this->actingAs(User::factory()->create())
            ->postJson(route('paypal.payment.create'), ['amount' => 25])
            ->assertStatus(422);
    }

    public function test_one_time_payment_requires_an_amount(): void
    {
        $this->actingAs(User::factory()->create())
            ->postJson(route('paypal.payment.create'), ['paymentMethodId' => 'pm_123'])
            ->assertStatus(422);
    }

    public function test_one_time_payment_delegates_to_the_paypal_gateway(): void
    {
        $this->bindPaypalGateway(['success' => true, 'transaction_id' => 'txn_test']);

        $response = $this->actingAs(User::factory()->create())
            ->postJson(route('paypal.payment.create'), [
                'paymentMethodId' => 'pm_123',
                'amount' => 25,
            ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'transaction_id' => 'txn_test',
            'payment_id' => 'pm_123',
        ]);
    }

    public function test_one_time_payment_returns_gateway_failure(): void
    {
        $this->bindPaypalGateway(['success' => false, 'error' => 'declined']);

        $response = $this->actingAs(User::factory()->create())
            ->postJson(route('paypal.payment.create'), [
                'paymentMethodId' => 'pm_bad',
                'amount' => 10,
            ]);

        $response->assertOk();
        $response->assertJson(['success' => false, 'error' => 'declined']);
    }
}
