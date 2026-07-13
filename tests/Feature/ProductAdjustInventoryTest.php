<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The admin "Adjust Inventory" / "Add Stock" actions must change stock atomically
 * (guarded increment/decrement), not read-modify-write — otherwise two staff, or an
 * adjustment racing a checkout decrement, lose one update. The floor-at-zero check
 * must also be atomic, and each change must leave a reconcilable old/new audit row.
 */
class ProductAdjustInventoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_positive_adjustment_increments_and_logs_old_and_new(): void
    {
        $product = Product::factory()->create(['inventory_count' => 20]);

        $this->assertTrue($product->adjustInventory(5, 'restock'));
        $this->assertEquals(25, $product->fresh()->inventory_count);
        $this->assertDatabaseHas('inventory_logs', [
            'product_id' => $product->id, 'quantity_change' => 5, 'old_quantity' => 20, 'new_quantity' => 25,
        ]);
    }

    public function test_negative_adjustment_decrements_and_logs_old_and_new(): void
    {
        $product = Product::factory()->create(['inventory_count' => 20]);

        $this->assertTrue($product->adjustInventory(-8, 'shrinkage'));
        $this->assertEquals(12, $product->fresh()->inventory_count);
        $this->assertDatabaseHas('inventory_logs', [
            'product_id' => $product->id, 'quantity_change' => -8, 'old_quantity' => 20, 'new_quantity' => 12,
        ]);
    }

    public function test_adjustment_below_zero_is_refused_and_leaves_stock_and_ledger_untouched(): void
    {
        $product = Product::factory()->create(['inventory_count' => 5]);

        $this->assertFalse($product->adjustInventory(-10, 'oversized decrease'));
        $this->assertEquals(5, $product->fresh()->inventory_count);
        $this->assertDatabaseMissing('inventory_logs', ['product_id' => $product->id, 'quantity_change' => -10]);
    }
}
