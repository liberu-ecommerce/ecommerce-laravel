<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\WebhookEndpoint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OutboundWebhookTest extends TestCase
{
    use RefreshDatabase;

    private function endpoint(array $events, bool $active = true): WebhookEndpoint
    {
        return WebhookEndpoint::create([
            'url' => 'https://example.test/hook',
            'secret' => 'whsec_local',
            'events' => $events,
            'is_active' => $active,
        ]);
    }

    private function pendingOrder(): Order
    {
        return Order::create(['customer_email' => 'b@example.com', 'total_amount' => 50, 'status' => 'pending']);
    }

    public function test_order_paid_delivers_a_signed_webhook_to_subscribers(): void
    {
        Http::fake();
        $this->endpoint(['order.paid']);
        $order = $this->pendingOrder();

        $order->transitionTo(Order::STATUS_PAID);

        Http::assertSent(function ($request) use ($order) {
            $signature = $request->header('X-Webhook-Signature')[0] ?? '';
            $signatureValid = hash_equals(hash_hmac('sha256', $request->body(), 'whsec_local'), $signature);

            return $request->url() === 'https://example.test/hook'
                && $request['event'] === 'order.paid'
                && $request['data']['id'] === $order->id
                && $request['data']['status'] === 'paid'
                && $signatureValid;
        });
    }

    public function test_endpoint_not_subscribed_to_the_event_receives_nothing(): void
    {
        Http::fake();
        $this->endpoint(['order.refunded']); // not subscribed to order.paid
        $this->pendingOrder()->transitionTo(Order::STATUS_PAID);

        Http::assertNothingSent();
    }

    public function test_inactive_endpoint_receives_nothing(): void
    {
        Http::fake();
        $this->endpoint(['order.paid'], active: false);
        $this->pendingOrder()->transitionTo(Order::STATUS_PAID);

        Http::assertNothingSent();
    }

    public function test_transition_with_no_endpoints_sends_nothing(): void
    {
        Http::fake();
        $this->pendingOrder()->transitionTo(Order::STATUS_PAID);

        Http::assertNothingSent();
    }
}
