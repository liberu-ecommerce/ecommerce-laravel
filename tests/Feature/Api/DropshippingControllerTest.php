<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Services\DropshippingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DropshippingControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    protected function actingAsUser(): void
    {
        Sanctum::actingAs($this->user);
    }

    public function test_suppliers_endpoint_requires_authentication()
    {
        $response = $this->getJson('/api/dropshipping/suppliers');

        $response->assertStatus(401);
    }

    public function test_suppliers_endpoint_returns_sanitized_list()
    {
        $this->actingAsUser();

        $response = $this->getJson('/api/dropshipping/suppliers');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'suppliers' => [
                    '*' => ['name', 'description'],
                ],
            ]);

        // Auth keys must not be exposed
        $suppliers = $response->json('suppliers');
        foreach ($suppliers as $supplier) {
            $this->assertArrayNotHasKey('auth', $supplier);
            $this->assertArrayNotHasKey('endpoints', $supplier);
        }
    }

    public function test_check_availability_requires_authentication()
    {
        $response = $this->postJson('/api/dropshipping/check-availability', [
            'supplier_id' => 'supplier1',
            'sku' => 'SKU-001',
        ]);

        $response->assertStatus(401);
    }

    public function test_check_availability_validates_required_fields()
    {
        $this->actingAsUser();

        $response = $this->postJson('/api/dropshipping/check-availability', []);

        $response->assertStatus(422)
            ->assertJson(['success' => false])
            ->assertJsonValidationErrors(['supplier_id', 'sku']);
    }

    public function test_check_availability_validates_quantity_is_integer()
    {
        $this->actingAsUser();

        $response = $this->postJson('/api/dropshipping/check-availability', [
            'supplier_id' => 'supplier1',
            'sku' => 'SKU-001',
            'quantity' => 'invalid',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['quantity']);
    }

    public function test_check_availability_calls_supplier_api()
    {
        $this->actingAsUser();

        Http::fake([
            '*' => Http::response(['available' => true, 'stock' => 50], 200),
        ]);

        $response = $this->postJson('/api/dropshipping/check-availability', [
            'supplier_id' => 'supplier1',
            'sku' => 'SKU-001',
            'quantity' => 2,
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    public function test_check_availability_returns_400_on_supplier_error()
    {
        $this->actingAsUser();

        Http::fake([
            '*' => Http::response('Bad Request', 400),
        ]);

        $response = $this->postJson('/api/dropshipping/check-availability', [
            'supplier_id' => 'supplier1',
            'sku' => 'SKU-001',
        ]);

        $response->assertStatus(400)
            ->assertJson(['success' => false]);
    }

    public function test_check_availability_returns_400_for_unknown_supplier()
    {
        $this->actingAsUser();

        $response = $this->postJson('/api/dropshipping/check-availability', [
            'supplier_id' => 'nonexistent_supplier',
            'sku' => 'SKU-001',
        ]);

        $response->assertStatus(400)
            ->assertJson(['success' => false]);
    }

    public function test_place_order_requires_authentication()
    {
        $response = $this->postJson('/api/dropshipping/place-order', [
            'supplier_id' => 'supplier1',
            'order_data' => [],
        ]);

        $response->assertStatus(401);
    }

    public function test_place_order_validates_required_fields()
    {
        $this->actingAsUser();

        $response = $this->postJson('/api/dropshipping/place-order', []);

        $response->assertStatus(422)
            ->assertJson(['success' => false])
            ->assertJsonValidationErrors(['supplier_id', 'order_data']);
    }

    public function test_place_order_validates_order_data_structure()
    {
        $this->actingAsUser();

        $response = $this->postJson('/api/dropshipping/place-order', [
            'supplier_id' => 'supplier1',
            'order_data' => ['some_field' => 'value'],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['order_data.items', 'order_data.shipping_address']);
    }

    public function test_place_order_calls_supplier_api()
    {
        $this->actingAsUser();

        Http::fake([
            '*' => Http::response(['id' => 'ORD-123', 'status' => 'accepted'], 200),
        ]);

        $response = $this->postJson('/api/dropshipping/place-order', [
            'supplier_id' => 'supplier1',
            'order_data' => [
                'items' => [
                    ['product_id' => 1, 'quantity' => 1, 'price' => 10.00],
                ],
                'shipping_address' => ['street' => '123 Main St', 'city' => 'Anytown'],
            ],
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    public function test_track_order_requires_authentication()
    {
        $response = $this->postJson('/api/dropshipping/track-order', [
            'supplier_id' => 'supplier1',
            'order_reference' => 'ORD-123',
        ]);

        $response->assertStatus(401);
    }

    public function test_track_order_validates_required_fields()
    {
        $this->actingAsUser();

        $response = $this->postJson('/api/dropshipping/track-order', []);

        $response->assertStatus(422)
            ->assertJson(['success' => false])
            ->assertJsonValidationErrors(['supplier_id', 'order_reference']);
    }

    public function test_track_order_calls_supplier_api()
    {
        $this->actingAsUser();

        Http::fake([
            '*' => Http::response(['status' => 'shipped', 'tracking_number' => 'TRK-999'], 200),
        ]);

        $response = $this->postJson('/api/dropshipping/track-order', [
            'supplier_id' => 'supplier1',
            'order_reference' => 'ORD-123',
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    public function test_track_order_returns_400_for_unknown_supplier()
    {
        $this->actingAsUser();

        $response = $this->postJson('/api/dropshipping/track-order', [
            'supplier_id' => 'nonexistent_supplier',
            'order_reference' => 'ORD-123',
        ]);

        $response->assertStatus(400)
            ->assertJson(['success' => false]);
    }
}
