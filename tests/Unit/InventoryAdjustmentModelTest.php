<?php

namespace Tests\Unit;

use App\Models\InventoryAdjustment;
use App\Models\InventoryItem;
use App\Models\InventoryLevel;
use App\Models\InventoryLocation;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryAdjustmentModelTest extends TestCase
{
    use RefreshDatabase;

    private function makeInventoryItem(): InventoryItem
    {
        $category = ProductCategory::create([
            'name' => 'IA Cat',
            'slug' => 'ia-cat-' . uniqid(),
        ]);
        $product = Product::create([
            'name' => 'IA Product',
            'slug' => 'ia-prod-' . uniqid(),
            'price' => 20.00,
            'category_id' => $category->id,
            'inventory_count' => 10,
        ]);
        return InventoryItem::create([
            'product_id' => $product->id,
            'sku' => 'SKU-IA-' . uniqid(),
            'tracked' => true,
            'requires_shipping' => true,
        ]);
    }

    private function makeLevel(InventoryItem $item): InventoryLevel
    {
        $location = InventoryLocation::create([
            'name' => 'Warehouse ' . uniqid(),
            'address1' => '100 Warehouse Dr',
            'city' => 'Chicago',
            'zip' => '60601',
            'country_code' => 'US',
            'legacy' => false,
            'active' => true,
        ]);
        return InventoryLevel::create([
            'inventory_item_id' => $item->id,
            'location_id' => $location->id,
            'available' => 100,
        ]);
    }

    private function makeAdjustment(InventoryItem $item, array $overrides = []): InventoryAdjustment
    {
        $level = $this->makeLevel($item);
        $base = [
            'inventory_level_id' => $level->id,
            'inventory_item_id' => $item->id,
            'quantity_delta' => 5,
            'available_after' => 105,
            'reason' => 'restock',
        ];
        return InventoryAdjustment::create(array_merge($base, $overrides));
    }

    public function test_adjustment_can_be_created(): void
    {
        $item = $this->makeInventoryItem();
        $adj = $this->makeAdjustment($item);

        $this->assertInstanceOf(InventoryAdjustment::class, $adj);
        $this->assertEquals(5, $adj->quantity_delta);
    }

    public function test_is_increase_returns_true_for_positive_delta(): void
    {
        $item = $this->makeInventoryItem();
        $adj = $this->makeAdjustment($item, ['quantity_delta' => 10]);

        $this->assertTrue($adj->isIncrease());
        $this->assertFalse($adj->isDecrease());
    }

    public function test_is_decrease_returns_true_for_negative_delta(): void
    {
        $item = $this->makeInventoryItem();
        $adj = $this->makeAdjustment($item, ['quantity_delta' => -3]);

        $this->assertTrue($adj->isDecrease());
        $this->assertFalse($adj->isIncrease());
    }

    public function test_quantity_delta_is_integer_cast(): void
    {
        $item = $this->makeInventoryItem();
        $adj = $this->makeAdjustment($item, ['quantity_delta' => -7]);

        $this->assertIsInt($adj->fresh()->quantity_delta);
    }

    public function test_adjust_quantity_updates_level_and_logs_adjustment(): void
    {
        $item = $this->makeInventoryItem();
        $level = $this->makeLevel($item); // available 100

        $level->adjustQuantity(10, 'restock');

        $level->refresh();
        $this->assertSame(110, $level->available);
        $this->assertSame(10, $level->on_hand);

        $adj = InventoryAdjustment::latest('id')->first();
        $this->assertNotNull($adj);
        // inventory_item_id is NOT NULL in the schema; adjustQuantity must set it.
        $this->assertSame($item->id, $adj->inventory_item_id);
        $this->assertSame(10, $adj->quantity_delta);
        $this->assertSame(110, $adj->available_after);
    }
}
