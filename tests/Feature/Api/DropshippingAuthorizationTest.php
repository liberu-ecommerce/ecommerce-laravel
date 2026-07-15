<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * The dropshipping endpoints spend the merchant's supplier API key on a caller-supplied
 * payload — they are staff-only. Any authenticated customer reaching them means real goods
 * ship anywhere, billed to the merchant.
 */
class DropshippingAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        Role::findOrCreate('super_admin', 'web');

        return User::factory()->create()->assignRole('super_admin');
    }

    /**
     * Valid payloads, so a 403 proves the role guard fired — not the validator.
     *
     * @return array<string, array{0: string, 1: string, 2: array<string, mixed>}>
     */
    public static function endpoints(): array
    {
        return [
            'suppliers' => ['getJson', '/api/dropshipping/suppliers', []],
            'check-availability' => ['postJson', '/api/dropshipping/check-availability', [
                'supplier_id' => 'dropxl',
                'sku' => 'SKU-1',
                'quantity' => 1,
            ]],
            'place-order' => ['postJson', '/api/dropshipping/place-order', [
                'supplier_id' => 'dropxl',
                'order_data' => [
                    'items' => [['sku' => 'SKU-1', 'quantity' => 1]],
                    'shipping_address' => ['line1' => '1 Attacker St', 'city' => 'Nowhere'],
                ],
            ]],
            'track-order' => ['postJson', '/api/dropshipping/track-order', [
                'supplier_id' => 'dropxl',
                'order_reference' => 'REF-1',
            ]],
        ];
    }

    #[DataProvider('endpoints')]
    public function test_authenticated_non_staff_user_is_forbidden(string $method, string $uri, array $payload): void
    {
        Http::preventStrayRequests(); // a 403 must happen before any supplier call
        Sanctum::actingAs(User::factory()->create());

        $this->{$method}($uri, $payload)->assertStatus(403);
    }

    #[DataProvider('endpoints')]
    public function test_super_admin_is_not_forbidden(string $method, string $uri, array $payload): void
    {
        Http::fake(); // no live supplier call; we only care that the guard lets staff through
        Sanctum::actingAs($this->admin());

        $this->assertNotSame(403, $this->{$method}($uri, $payload)->status());
    }

    public function test_unauthenticated_user_is_rejected(): void
    {
        $this->getJson('/api/dropshipping/suppliers')->assertStatus(401);
    }
}
