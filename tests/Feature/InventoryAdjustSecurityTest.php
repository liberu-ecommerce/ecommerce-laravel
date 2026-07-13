<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class InventoryAdjustSecurityTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        Role::findOrCreate('super_admin', 'web');

        return User::factory()->create()->assignRole('super_admin');
    }

    private function product(int $stock = 10): Product
    {
        return Product::factory()->create(['inventory_count' => $stock]);
    }

    public function test_adjust_requires_authentication(): void
    {
        $product = $this->product();

        $this->postJson('/inventory/adjust', [
            'product_id' => $product->id, 'quantity_change' => 5, 'reason' => 'x',
        ])->assertStatus(401);

        $this->assertEquals(10, $product->fresh()->inventory_count);
    }

    public function test_non_admin_cannot_adjust(): void
    {
        $product = $this->product();

        $this->actingAs(User::factory()->create())->postJson('/inventory/adjust', [
            'product_id' => $product->id, 'quantity_change' => 5, 'reason' => 'x',
        ])->assertStatus(403);

        $this->assertEquals(10, $product->fresh()->inventory_count);
    }

    public function test_admin_can_adjust_and_writes_full_log(): void
    {
        $admin = $this->admin();
        $product = $this->product(10);

        $this->actingAs($admin)->postJson('/inventory/adjust', [
            'product_id' => $product->id, 'quantity_change' => 5, 'reason' => 'restock',
        ])->assertStatus(200);

        $this->assertEquals(15, $product->fresh()->inventory_count);
        $this->assertDatabaseHas('inventory_logs', [
            'product_id' => $product->id, 'quantity_change' => 5,
            'old_quantity' => 10, 'new_quantity' => 15, 'reason' => 'restock',
        ]);
    }

    public function test_soft_deleted_product_returns_404_not_500(): void
    {
        $admin = $this->admin();
        $product = $this->product();
        $product->delete(); // soft delete — passes the exists rule but not findOrFail

        $this->actingAs($admin)->postJson('/inventory/adjust', [
            'product_id' => $product->id, 'quantity_change' => 5, 'reason' => 'x',
        ])->assertStatus(404);
    }
}
