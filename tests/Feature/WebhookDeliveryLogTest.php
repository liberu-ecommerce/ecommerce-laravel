<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\WebhookDelivery;
use App\Models\WebhookEndpoint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WebhookDeliveryLogTest extends TestCase
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

    private function pendingOrder(): Order
    {
        return Order::create(['customer_email' => 'b@example.com', 'total_amount' => 50, 'status' => 'pending']);
    }

    public function test_a_successful_delivery_is_logged(): void
    {
        Http::fake(['*' => Http::response('', 200)]);
        $endpoint = $this->endpoint();

        $this->pendingOrder()->transitionTo(Order::STATUS_PAID);

        $this->assertDatabaseHas('webhook_deliveries', [
            'webhook_endpoint_id' => $endpoint->id,
            'event' => 'order.paid',
            'success' => true,
            'status_code' => 200,
            'attempt' => 1,
        ]);
        $this->assertSame(1, WebhookDelivery::count());
    }

    public function test_a_failing_delivery_is_retried_a_bounded_number_of_times_and_logged(): void
    {
        Http::fake(['*' => Http::response('nope', 500)]);
        $endpoint = $this->endpoint();

        // Must NOT throw out of the transition even though every attempt fails.
        $this->pendingOrder()->transitionTo(Order::STATUS_PAID);

        // 3 bounded attempts, all logged as failures.
        $this->assertSame(3, WebhookDelivery::where('webhook_endpoint_id', $endpoint->id)->count());
        $this->assertSame(0, WebhookDelivery::where('success', true)->count());
        $this->assertEqualsCanonicalizing([1, 2, 3], WebhookDelivery::pluck('attempt')->all());
    }

    public function test_a_connection_error_is_recorded_not_fatal(): void
    {
        Http::fake(fn () => throw new ConnectionException('connection refused'));
        $endpoint = $this->endpoint();

        $this->pendingOrder()->transitionTo(Order::STATUS_PAID);

        $delivery = WebhookDelivery::where('webhook_endpoint_id', $endpoint->id)->first();
        $this->assertNotNull($delivery);
        $this->assertFalse($delivery->success);
        $this->assertNull($delivery->status_code);
        $this->assertStringContainsString('connection refused', $delivery->error);
    }
}
