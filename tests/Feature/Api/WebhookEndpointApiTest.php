<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Models\WebhookEndpoint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class WebhookEndpointApiTest extends TestCase
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

    public function test_unauthenticated_cannot_manage_endpoints(): void
    {
        $this->getJson('/api/webhook-endpoints')->assertStatus(401);
    }

    public function test_non_admin_cannot_manage_endpoints(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $this->getJson('/api/webhook-endpoints')->assertStatus(403);
        $this->postJson('/api/webhook-endpoints', ['url' => 'https://x.test', 'events' => ['order.paid']])->assertStatus(403);
    }

    public function test_admin_creates_an_endpoint_with_a_generated_secret(): void
    {
        Sanctum::actingAs($this->admin());

        $response = $this->postJson('/api/webhook-endpoints', [
            'url' => 'https://partner.test/hook',
            'events' => ['order.paid', 'order.refunded'],
        ])->assertStatus(201);

        $secret = $response->json('secret');
        $this->assertStringStartsWith('whsec_', $secret); // generated server-side, returned once
        $this->assertDatabaseHas('webhook_endpoints', ['url' => 'https://partner.test/hook', 'is_active' => true]);
    }

    public function test_create_validates_url_and_events(): void
    {
        Sanctum::actingAs($this->admin());

        $this->postJson('/api/webhook-endpoints', ['url' => 'not-a-url', 'events' => ['order.paid']])->assertStatus(422);
        $this->postJson('/api/webhook-endpoints', ['url' => 'https://x.test', 'events' => []])->assertStatus(422);
    }

    public function test_admin_lists_endpoints(): void
    {
        $this->endpoint();
        Sanctum::actingAs($this->admin());

        $this->getJson('/api/webhook-endpoints')->assertStatus(200)->assertJsonPath('total', 1);
    }

    public function test_admin_updates_an_endpoint(): void
    {
        $endpoint = $this->endpoint();
        Sanctum::actingAs($this->admin());

        $this->putJson("/api/webhook-endpoints/{$endpoint->id}", ['is_active' => false, 'events' => ['order.cancelled']])
            ->assertStatus(200);

        $endpoint->refresh();
        $this->assertFalse($endpoint->is_active);
        $this->assertSame(['order.cancelled'], $endpoint->events);
    }

    public function test_admin_deletes_an_endpoint(): void
    {
        $endpoint = $this->endpoint();
        Sanctum::actingAs($this->admin());

        $this->deleteJson("/api/webhook-endpoints/{$endpoint->id}")->assertStatus(200);
        $this->assertDatabaseMissing('webhook_endpoints', ['id' => $endpoint->id]);
    }
}
