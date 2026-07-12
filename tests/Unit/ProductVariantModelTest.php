<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductVariantModelTest extends TestCase
{
    use RefreshDatabase;

    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        $category = ProductCategory::create([
            'name' => 'Variant Cat',
            'slug' => 'variant-cat-' . uniqid(),
        ]);
        $this->product = Product::create([
            'name' => 'Main Product',
            'slug' => 'main-prod-' . uniqid(),
            'price' => 50.00,
            'category_id' => $category->id,
            'inventory_count' => 0,
        ]);
    }

    private function makeVariant(array $overrides = []): ProductVariant
    {
        return ProductVariant::create(array_merge([
            'product_id' => $this->product->id,
            'sku' => 'SKU-' . uniqid(),
            'title' => 'Red / Large',
            'price' => 55.00,
            'inventory_quantity' => 10,
            'taxable' => true,
            'requires_shipping' => true,
            'position' => 1,
        ], $overrides));
    }

    public function test_is_in_stock_returns_true_when_inventory_positive(): void
    {
        $variant = $this->makeVariant(['inventory_quantity' => 5]);

        $this->assertTrue($variant->isInStock());
    }

    public function test_is_in_stock_returns_false_when_inventory_zero(): void
    {
        $variant = $this->makeVariant(['inventory_quantity' => 0]);

        $this->assertFalse($variant->isInStock());
    }

    public function test_get_display_title_uses_title_when_set(): void
    {
        $variant = $this->makeVariant(['title' => 'Blue / Small', 'option1' => null]);

        $this->assertEquals('Blue / Small', $variant->display_title);
    }

    public function test_get_display_title_uses_options_when_no_title(): void
    {
        $variant = $this->makeVariant([
            'title' => null,
            'option1' => 'Red',
            'option2' => 'Large',
            'option3' => null,
        ]);

        $this->assertEquals('Red / Large', $variant->display_title);
    }

    public function test_in_stock_scope_filters_correctly(): void
    {
        $inStock = $this->makeVariant(['inventory_quantity' => 5, 'sku' => 'in-stock-' . uniqid()]);
        $outOfStock = $this->makeVariant(['inventory_quantity' => 0, 'sku' => 'out-' . uniqid()]);

        $results = ProductVariant::inStock()->pluck('id');

        $this->assertContains($inStock->id, $results);
        $this->assertNotContains($outOfStock->id, $results);
    }

    public function test_belongs_to_product(): void
    {
        $variant = $this->makeVariant();

        $this->assertInstanceOf(Product::class, $variant->product);
        $this->assertEquals($this->product->id, $variant->product->id);
    }

    public function test_price_stores_decimal_not_integer(): void
    {
        // Locks the money column as decimal(10,2): an integer column would
        // truncate 19.99 -> 19/20 and this round-trip would fail.
        $variant = $this->makeVariant(['price' => 19.99, 'compare_at_price' => 29.95]);

        $fresh = $variant->fresh();
        $this->assertEqualsWithDelta(19.99, (float) $fresh->price, 0.001);
        $this->assertEqualsWithDelta(29.95, (float) $fresh->compare_at_price, 0.001);
    }

    public function test_casts_are_correct(): void
    {
        $variant = $this->makeVariant([
            'price' => 29.99,
            'taxable' => true,
            'requires_shipping' => false,
            'inventory_quantity' => 3,
        ]);

        $fresh = $variant->fresh();
        $this->assertIsFloat((float) $fresh->price);
        $this->assertIsBool($fresh->taxable);
        $this->assertIsBool($fresh->requires_shipping);
        $this->assertIsInt($fresh->inventory_quantity);
    }
}
