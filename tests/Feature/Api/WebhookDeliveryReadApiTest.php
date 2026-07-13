<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Models\WebhookDelivery;
use App\Models\WebhookEndpoint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class WebhookDeliveryReadApiTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        Role::findOrCreate('super_admin', 'web');

        return User::factory()->create()->assignRole('super_admin');
    }

    private function endpoint(): WebhookEndpoint
    {
        return WebhookEndpoint::create([
            'url' => 'https://example.test/hook',
            'secret' => 'whsec_seed',
            'events' => ['order.paid'],
            'is_active' => true,
        ]);
    }

    private function delivery(WebhookEndpoint $endpoint, bool $success, int $status = 200): WebhookDelivery
    {
        return $endpoint->deliveries()->create([
            'order_id' => 1,
            'event' => 'order.paid',
            'status_code' => $status,
            'success' => $success,
            'attempt' => 1,
        ]);
    }

    public function test_non_admin_cannot_read_deliveries(): void
    {
        $endpoint = $this->endpoint();
        Sanctum::actingAs(User::factory()->create());

        $this->getJson("/api/webhook-endpoints/{$endpoint->id}/deliveries")->assertStatus(403);
    }

    public function test_admin_reads_deliveries_for_an_endpoint_only(): void
    {
        $endpoint = $this->endpoint();
        $other = $this->endpoint();
        $this->delivery($endpoint, true);
        $this->delivery($endpoint, false, 500);
        $this->delivery($other, true);

        Sanctum::actingAs($this->admin());
        $response = $this->getJson("/api/webhook-endpoints/{$endpoint->id}/deliveries");

        $response->assertStatus(200)->assertJsonPath('total', 2);
        $endpointIds = collect($response->json('data'))->pluck('webhook_endpoint_id')->unique()->all();
        $this->assertSame([$endpoint->id], $endpointIds);
    }

    public function test_deliveries_can_be_filtered_by_success(): void
    {
        $endpoint = $this->endpoint();
        $this->delivery($endpoint, true);
        $this->delivery($endpoint, false, 500);
        $this->delivery($endpoint, false, 502);

        Sanctum::actingAs($this->admin());

        $this->getJson("/api/webhook-endpoints/{$endpoint->id}/deliveries?success=0")
            ->assertStatus(200)->assertJsonPath('total', 2);
        $this->getJson("/api/webhook-endpoints/{$endpoint->id}/deliveries?success=1")
            ->assertStatus(200)->assertJsonPath('total', 1);
    }
}
