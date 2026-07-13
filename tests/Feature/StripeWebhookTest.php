<?php

namespace Tests\Feature;

use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class StripeWebhookTest extends TestCase
{
    use RefreshDatabase;

    private string $secret = 'whsec_test_secret';

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
        config(['services.stripe.webhook.secret' => $this->secret]);
    }

    private function order(string $status, string $charge = 'ch_1', float $total = 100): Order
    {
        return Order::create([
            'customer_email' => 'buyer@example.com',
            'payment_method' => 'stripe',
            'transaction_id' => $charge,
            'total_amount' => $total,
            'status' => $status,
        ]);
    }

    private function postWebhook(array $event, ?string $secret = null): TestResponse
    {
        $payload = json_encode(array_merge(['id' => 'evt_1', 'object' => 'event', 'created' => time()], $event));
        $ts = time();
        $sig = 't='.$ts.',v1='.hash_hmac('sha256', $ts.'.'.$payload, $secret ?? $this->secret);

        return $this->call('POST', '/stripe/webhook', [], [], [], ['HTTP_STRIPE_SIGNATURE' => $sig], $payload);
    }

    private function charge(array $overrides = []): array
    {
        return array_merge(['id' => 'ch_1', 'object' => 'charge', 'amount' => 10000, 'amount_refunded' => 0], $overrides);
    }

    public function test_invalid_signature_is_rejected(): void
    {
        $response = $this->postWebhook(
            ['type' => 'charge.succeeded', 'data' => ['object' => $this->charge()]],
            secret: 'wrong_secret'
        );

        $response->assertStatus(400);
    }

    public function test_charge_succeeded_marks_a_pending_order_paid(): void
    {
        $order = $this->order(Order::STATUS_PENDING);

        $this->postWebhook(['type' => 'charge.succeeded', 'data' => ['object' => $this->charge()]])
            ->assertStatus(200);

        $this->assertSame(Order::STATUS_PAID, $order->refresh()->status);
    }

    public function test_charge_succeeded_is_idempotent_for_an_already_paid_order(): void
    {
        $order = $this->order(Order::STATUS_PAID);

        $this->postWebhook(['type' => 'charge.succeeded', 'data' => ['object' => $this->charge()]])
            ->assertStatus(200);

        $this->assertSame(Order::STATUS_PAID, $order->refresh()->status);
    }

    public function test_charge_refunded_fully_reconciles_the_order(): void
    {
        $order = $this->order(Order::STATUS_PAID, total: 100);

        $this->postWebhook(['type' => 'charge.refunded', 'data' => ['object' => $this->charge(['amount_refunded' => 10000])]])
            ->assertStatus(200);

        $order->refresh();
        $this->assertSame(Order::STATUS_REFUNDED, $order->status);
        $this->assertSame(100.0, (float) $order->refund_total);
        $this->assertTrue((bool) $order->fully_refunded);
    }

    public function test_charge_refunded_partially_reconciles_the_order(): void
    {
        $order = $this->order(Order::STATUS_PAID, total: 100);

        $this->postWebhook(['type' => 'charge.refunded', 'data' => ['object' => $this->charge(['amount_refunded' => 4000])]])
            ->assertStatus(200);

        $order->refresh();
        $this->assertSame(Order::STATUS_PARTIALLY_REFUNDED, $order->status);
        $this->assertSame(40.0, (float) $order->refund_total);
    }

    public function test_charge_failed_marks_a_pending_order_failed(): void
    {
        $order = $this->order(Order::STATUS_PENDING);

        $this->postWebhook(['type' => 'charge.failed', 'data' => ['object' => $this->charge()]])
            ->assertStatus(200);

        $this->assertSame(Order::STATUS_FAILED, $order->refresh()->status);
    }

    public function test_unknown_event_is_acknowledged(): void
    {
        $this->postWebhook(['type' => 'customer.created', 'data' => ['object' => ['id' => 'cus_1', 'object' => 'customer']]])
            ->assertStatus(200)
            ->assertJsonPath('received', true);
    }
}
