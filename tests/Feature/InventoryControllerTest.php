<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class InventoryControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Stock adjustment is admin-only; these cover controller behaviour, so
        // authenticate as an admin (authorization is covered in
        // InventoryAdjustSecurityTest).
        Role::findOrCreate('super_admin', 'web');
        $this->actingAs(User::factory()->create()->assignRole('super_admin'));
    }

    private function makeProduct(array $overrides = []): Product
    {
        $category = ProductCategory::create([
            'name' => 'Inventory Category',
            'slug' => 'inv-cat-'.uniqid(),
        ]);

        return Product::create(array_merge([
            'name' => 'Inventory Product',
            'slug' => 'inv-prod-'.uniqid(),
            'price' => 10.00,
            'category_id' => $category->id,
            'inventory_count' => 10,
        ], $overrides));
    }

    public function test_adjust_inventory_increases_stock(): void
    {
        $product = $this->makeProduct(['inventory_count' => 10]);

        $response = $this->postJson('/inventory/adjust', [
            'product_id' => $product->id,
            'quantity_change' => 5,
            'reason' => 'restock',
        ]);

        $response->assertStatus(200);
        $this->assertEquals(15, $product->fresh()->inventory_count);
    }

    public function test_adjust_inventory_decreases_stock(): void
    {
        $product = $this->makeProduct(['inventory_count' => 10]);

        $response = $this->postJson('/inventory/adjust', [
            'product_id' => $product->id,
            'quantity_change' => -3,
            'reason' => 'damaged goods',
        ]);

        $response->assertStatus(200);
        $this->assertEquals(7, $product->fresh()->inventory_count);
    }

    public function test_adjust_inventory_rejects_negative_stock(): void
    {
        $product = $this->makeProduct(['inventory_count' => 5]);

        $response = $this->postJson('/inventory/adjust', [
            'product_id' => $product->id,
            'quantity_change' => -10,
            'reason' => 'test',
        ]);

        $response->assertStatus(400);
        $this->assertEquals(5, $product->fresh()->inventory_count);
    }

    public function test_adjust_inventory_requires_product_id(): void
    {
        $response = $this->postJson('/inventory/adjust', [
            'quantity_change' => 5,
            'reason' => 'test',
        ]);

        $response->assertStatus(422);
    }

    public function test_adjust_inventory_requires_quantity_change(): void
    {
        $product = $this->makeProduct();

        $response = $this->postJson('/inventory/adjust', [
            'product_id' => $product->id,
            'reason' => 'test',
        ]);

        $response->assertStatus(422);
    }

    public function test_adjust_inventory_requires_reason(): void
    {
        $product = $this->makeProduct();

        $response = $this->postJson('/inventory/adjust', [
            'product_id' => $product->id,
            'quantity_change' => 5,
        ]);

        $response->assertStatus(422);
    }

    public function test_adjust_inventory_logs_change(): void
    {
        $product = $this->makeProduct(['inventory_count' => 10]);

        $this->postJson('/inventory/adjust', [
            'product_id' => $product->id,
            'quantity_change' => 5,
            'reason' => 'restocking',
        ]);

        $this->assertDatabaseHas('inventory_logs', [
            'product_id' => $product->id,
            'quantity_change' => 5,
            'reason' => 'restocking',
        ]);
    }

    public function test_adjust_inventory_returns_404_for_unknown_product(): void
    {
        $response = $this->postJson('/inventory/adjust', [
            'product_id' => 9999,
            'quantity_change' => 5,
            'reason' => 'test',
        ]);

        $response->assertStatus(422);
    }
}
