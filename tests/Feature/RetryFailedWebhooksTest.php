<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\WebhookDelivery;
use App\Models\WebhookEndpoint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class RetryFailedWebhooksTest extends TestCase
{
    use RefreshDatabase;

    private function endpoint(): WebhookEndpoint
    {
        return WebhookEndpoint::create([
            'url' => 'https://example.test/hook',
            'secret' => 'whsec_local',
            'events' => ['order.paid'],
            'is_active' => true,
        ]);
    }

    private function failedDelivery(WebhookEndpoint $endpoint, int $orderId = 1): WebhookDelivery
    {
        return $endpoint->deliveries()->create([
            'order_id' => $orderId,
            'event' => 'order.paid',
            'status_code' => 500,
            'success' => false,
            'attempt' => 3,
        ]);
    }

    public function test_it_requeues_delivery_for_a_still_failing_tuple(): void
    {
        Http::fake(['*' => Http::response('', 200)]); // receiver is back up now
        $endpoint = $this->endpoint();
        $order = Order::create(['customer_email' => 'b@x.com', 'total_amount' => 10, 'status' => 'paid']);
        $this->failedDelivery($endpoint, $order->id);

        $this->artisan('webhooks:retry-failed')->assertExitCode(0);

        // A fresh successful delivery is recorded for the previously-failing tuple.
        $this->assertTrue(
            WebhookDelivery::where('webhook_endpoint_id', $endpoint->id)
                ->where('order_id', $order->id)->where('event', 'order.paid')
                ->where('success', true)->exists()
        );
    }

    public function test_it_does_not_requeue_a_tuple_that_already_succeeded(): void
    {
        Http::fake(['*' => Http::response('', 200)]);
        $endpoint = $this->endpoint();
        $this->failedDelivery($endpoint, 7);
        // A later success for the same tuple.
        $endpoint->deliveries()->create(['order_id' => 7, 'event' => 'order.paid', 'status_code' => 200, 'success' => true, 'attempt' => 4]);

        $this->artisan('webhooks:retry-failed')->assertExitCode(0);

        Http::assertNothingSent(); // nothing re-queued
    }

    public function test_it_ignores_failures_outside_the_retry_window(): void
    {
        Http::fake(['*' => Http::response('', 200)]);
        $endpoint = $this->endpoint();
        $old = $this->failedDelivery($endpoint, 9);
        $old->forceFill(['created_at' => now()->subDays(3)])->saveQuietly();

        $this->artisan('webhooks:retry-failed')->assertExitCode(0);

        Http::assertNothingSent();
    }
}
