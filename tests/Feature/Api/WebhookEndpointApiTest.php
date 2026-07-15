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

    public function test_create_rejects_non_https_and_private_network_urls(): void
    {
        Sanctum::actingAs($this->admin());

        $unsafe = [
            'http://partner.test/hook',                  // plaintext
            'ftp://partner.test/hook',                   // wrong scheme
            'https://127.0.0.1/hook',                    // loopback
            'https://169.254.169.254/latest/meta-data/', // cloud metadata
            'https://192.168.1.10/hook',                 // private LAN
            'https://[::1]/hook',                        // IPv6 loopback
        ];

        foreach ($unsafe as $url) {
            $response = $this->postJson('/api/webhook-endpoints', ['url' => $url, 'events' => ['order.paid']]);
            $this->assertSame(422, $response->status(), "expected {$url} to be rejected as unsafe");
        }

        $this->assertDatabaseCount('webhook_endpoints', 0);
    }

    public function test_admin_lists_endpoints(): void
    {
        $this->endpoint();
        Sanctum::actingAs($this->admin());

        $this->getJson('/api/webhook-endpoints')->assertStatus(200)->assertJsonPath('total', 1);
    }

    public function test_listing_endpoints_does_not_expose_the_signing_secret(): void
    {
        $this->endpoint();
        Sanctum::actingAs($this->admin());

        $response = $this->getJson('/api/webhook-endpoints')->assertStatus(200);

        $this->assertNull($response->json('data.0.secret'));
        $response->assertJsonMissing(['secret' => 'whsec_seed']);
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

    public function test_updating_an_endpoint_does_not_expose_the_signing_secret(): void
    {
        $endpoint = $this->endpoint();
        Sanctum::actingAs($this->admin());

        $response = $this->putJson("/api/webhook-endpoints/{$endpoint->id}", ['is_active' => false])->assertStatus(200);

        $this->assertNull($response->json('secret'));
        $this->assertSame('whsec_seed', $endpoint->fresh()->secret); // still stored, just not serialised
    }

    public function test_update_rejects_private_network_urls(): void
    {
        $endpoint = $this->endpoint();
        Sanctum::actingAs($this->admin());

        $this->putJson("/api/webhook-endpoints/{$endpoint->id}", ['url' => 'https://169.254.169.254/latest/meta-data/'])
            ->assertStatus(422);

        $this->assertSame('https://example.test/hook', $endpoint->fresh()->url);
    }

    public function test_admin_deletes_an_endpoint(): void
    {
        $endpoint = $this->endpoint();
        Sanctum::actingAs($this->admin());

        $this->deleteJson("/api/webhook-endpoints/{$endpoint->id}")->assertStatus(200);
        $this->assertDatabaseMissing('webhook_endpoints', ['id' => $endpoint->id]);
    }
}
