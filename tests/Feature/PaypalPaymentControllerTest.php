<?php

namespace Tests\Feature;

use App\Interfaces\PaymentGatewayInterface;
use App\Models\User;
use App\Services\PaymentGateways\PayPalGateway;
use App\Services\SubscriptionService;
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

    /** Bind a SubscriptionService that returns a canned PayPal success without HTTP. */
    private function fakeSubscriptionService(array $return): void
    {
        $this->app->instance(SubscriptionService::class, new class($return) extends SubscriptionService
        {
            public function __construct(private array $return) {}

            public function createSubscription($paymentMethodId, $planId, $userDetails)
            {
                return $this->return;
            }
        });
    }

    public function test_create_subscription_persists_it_owned_by_the_user(): void
    {
        $this->fakeSubscriptionService([
            'success' => true,
            'subscription_id' => 'I-SUB999',
            'status' => 'APPROVAL_PENDING',
            'approval_url' => 'https://paypal.com/approve/I-SUB999',
        ]);
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson(route('paypal.subscription.create'), [
                'paymentMethodId' => 'pm_1',
                'planId' => 'P-PLAN',
            ])
            ->assertOk()
            ->assertJson(['success' => true, 'subscription_id' => 'I-SUB999']);

        $this->assertDatabaseHas('paypal_subscriptions', [
            'user_id' => $user->id,
            'paypal_subscription_id' => 'I-SUB999',
            'plan_id' => 'P-PLAN',
            'status' => 'APPROVAL_PENDING',
        ]);
    }

    public function test_create_subscription_requires_authentication(): void
    {
        $this->postJson(route('paypal.subscription.create'), [
            'paymentMethodId' => 'pm_1',
            'planId' => 'P-PLAN',
        ])->assertUnauthorized();
    }

    public function test_create_subscription_requires_a_plan_id(): void
    {
        $this->actingAs(User::factory()->create())
            ->postJson(route('paypal.subscription.create'), ['paymentMethodId' => 'pm_1'])
            ->assertStatus(422);
    }

    public function test_failed_subscription_is_not_persisted(): void
    {
        $this->fakeSubscriptionService(['success' => false, 'error' => 'Invalid plan']);
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson(route('paypal.subscription.create'), [
                'paymentMethodId' => 'pm_1',
                'planId' => 'P-BAD',
            ])
            ->assertOk()
            ->assertJson(['success' => false]);

        $this->assertDatabaseCount('paypal_subscriptions', 0);
    }
}
