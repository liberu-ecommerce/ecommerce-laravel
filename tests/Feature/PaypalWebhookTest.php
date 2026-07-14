<?php

namespace Tests\Feature;

use App\Models\PaypalSubscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Srmklive\PayPal\Services\PayPal as PayPalClient;
use Tests\TestCase;

/**
 * PayPal subscription webhook: signature-verified, then maps subscription lifecycle
 * events onto the local PaypalSubscription status. Unauthenticated + CSRF-exempt
 * (PayPal is the caller), so the signature check is the only gate.
 */
class PaypalWebhookTest extends TestCase
{
    use RefreshDatabase;

    /** Bind a PayPal client whose local signature verification returns $verified. */
    private function fakeVerification(bool $verified): void
    {
        config(['paypal.webhook_id' => 'WH-TEST']);

        $client = Mockery::mock(PayPalClient::class);
        $client->shouldReceive('verifyWebHookLocally')->andReturn($verified);
        $this->app->instance(PayPalClient::class, $client);
    }

    private function subscription(string $status = 'APPROVAL_PENDING'): PaypalSubscription
    {
        return PaypalSubscription::create([
            'user_id' => User::factory()->create()->id,
            'paypal_subscription_id' => 'I-SUB123',
            'plan_id' => 'P-PLAN',
            'status' => $status,
        ]);
    }

    private function event(string $type, string $subId = 'I-SUB123'): array
    {
        return ['event_type' => $type, 'resource' => ['id' => $subId]];
    }

    public function test_invalid_signature_is_rejected_and_changes_nothing(): void
    {
        $this->fakeVerification(false);
        $sub = $this->subscription();

        $this->postJson(route('paypal.webhook'), $this->event('BILLING.SUBSCRIPTION.ACTIVATED'))
            ->assertStatus(400);

        $this->assertSame('APPROVAL_PENDING', $sub->fresh()->status);
    }

    public function test_activated_event_marks_the_subscription_active(): void
    {
        $this->fakeVerification(true);
        $sub = $this->subscription();

        $this->postJson(route('paypal.webhook'), $this->event('BILLING.SUBSCRIPTION.ACTIVATED'))
            ->assertOk();

        $this->assertSame('ACTIVE', $sub->fresh()->status);
    }

    public function test_cancelled_event_marks_the_subscription_cancelled(): void
    {
        $this->fakeVerification(true);
        $sub = $this->subscription('ACTIVE');

        $this->postJson(route('paypal.webhook'), $this->event('BILLING.SUBSCRIPTION.CANCELLED'))
            ->assertOk();

        $this->assertSame('CANCELLED', $sub->fresh()->status);
    }

    public function test_unmapped_event_is_acknowledged_without_changing_status(): void
    {
        $this->fakeVerification(true);
        $sub = $this->subscription('ACTIVE');

        $this->postJson(route('paypal.webhook'), $this->event('PAYMENT.SALE.COMPLETED'))
            ->assertOk();

        $this->assertSame('ACTIVE', $sub->fresh()->status);
    }

    public function test_event_for_an_unknown_subscription_is_acknowledged(): void
    {
        $this->fakeVerification(true);

        $this->postJson(route('paypal.webhook'), $this->event('BILLING.SUBSCRIPTION.ACTIVATED', 'I-DOES-NOT-EXIST'))
            ->assertOk();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
