<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Models\ProductBundle;
use App\Models\ProductBundleItem;
use App\Models\ProductCategory;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductBundleItemModelTest extends TestCase
{
    use RefreshDatabase;

    private function makeProduct(): Product
    {
        $category = ProductCategory::create([
            'name' => 'Bundle Item Cat',
            'slug' => 'bundle-item-cat-' . uniqid(),
        ]);
        return Product::create([
            'name' => 'Bundle Item Product',
            'slug' => 'bundle-item-prod-' . uniqid(),
            'price' => 20.00,
            'category_id' => $category->id,
            'inventory_count' => 10,
        ]);
    }

    private function makeBundle(Product $product): ProductBundle
    {
        return ProductBundle::create([
            'product_id' => $product->id,
            'name' => 'Test Bundle',
            'discount_amount' => 0,
            'discount_percentage' => 0,
            'is_active' => true,
        ]);
    }

    public function test_bundle_item_can_be_created(): void
    {
        $product = $this->makeProduct();
        $bundle = $this->makeBundle($product);
        $itemProduct = $this->makeProduct();

        $item = ProductBundleItem::create([
            'bundle_id' => $bundle->id,
            'product_id' => $itemProduct->id,
            'quantity' => 2,
            'sort_order' => 1,
        ]);

        $this->assertInstanceOf(ProductBundleItem::class, $item);
        $this->assertEquals(2, $item->quantity);
    }

    public function test_bundle_item_belongs_to_product(): void
    {
        $product = $this->makeProduct();
        $bundle = $this->makeBundle($product);
        $itemProduct = $this->makeProduct();

        $item = ProductBundleItem::create([
            'bundle_id' => $bundle->id,
            'product_id' => $itemProduct->id,
            'quantity' => 1,
            'sort_order' => 1,
        ]);

        $this->assertInstanceOf(Product::class, $item->product);
        $this->assertEquals($itemProduct->id, $item->product->id);
    }

    public function test_bundle_item_belongs_to_bundle(): void
    {
        $product = $this->makeProduct();
        $bundle = $this->makeBundle($product);
        $itemProduct = $this->makeProduct();

        $item = ProductBundleItem::create([
            'bundle_id' => $bundle->id,
            'product_id' => $itemProduct->id,
            'quantity' => 1,
            'sort_order' => 1,
        ]);

        $this->assertInstanceOf(ProductBundle::class, $item->bundle);
        $this->assertEquals($bundle->id, $item->bundle->id);
    }

    public function test_get_price_uses_product_price_with_quantity_and_discount(): void
    {
        $product = $this->makeProduct(); // price 20.00
        $bundle = $this->makeBundle($product);
        $itemProduct = $this->makeProduct(); // price 20.00

        $item = ProductBundleItem::create([
            'bundle_id' => $bundle->id,
            'product_id' => $itemProduct->id,
            'quantity' => 3,
            'discount_amount' => 5,
            'sort_order' => 1,
        ]);

        // 20 * 3 - 5 = 55
        $this->assertEqualsWithDelta(55.0, $item->getPrice(), 0.01);
    }

    public function test_get_price_prefers_variant_price_over_product_price(): void
    {
        $product = $this->makeProduct();
        $bundle = $this->makeBundle($product);
        $variantProduct = $this->makeProduct(); // product price 20.00
        $variant = ProductVariant::create([
            'product_id' => $variantProduct->id,
            'sku' => 'VAR-' . uniqid(),
            'price' => 12.50,
            'inventory_quantity' => 10,
        ]);

        $item = ProductBundleItem::create([
            'bundle_id' => $bundle->id,
            'product_id' => $variantProduct->id,
            'product_variant_id' => $variant->id,
            'quantity' => 2,
            'discount_amount' => 0,
            'sort_order' => 1,
        ]);

        // Uses the 12.50 variant price, not the 20.00 product price: 12.50 * 2 = 25
        $this->assertEqualsWithDelta(25.0, $item->getPrice(), 0.01);
        $this->assertInstanceOf(ProductVariant::class, $item->variant);
    }

    public function test_get_price_never_negative_when_discount_exceeds_value(): void
    {
        $product = $this->makeProduct();
        $bundle = $this->makeBundle($product);
        $itemProduct = $this->makeProduct(); // price 20.00

        $item = ProductBundleItem::create([
            'bundle_id' => $bundle->id,
            'product_id' => $itemProduct->id,
            'quantity' => 1,
            'discount_amount' => 999,
            'sort_order' => 1,
        ]);

        $this->assertEqualsWithDelta(0.0, $item->getPrice(), 0.01);
    }

    public function test_items_ordered_by_sort_order(): void
    {
        $product = $this->makeProduct();
        $bundle = $this->makeBundle($product);
        $prod1 = $this->makeProduct();
        $prod2 = $this->makeProduct();

        ProductBundleItem::create(['bundle_id' => $bundle->id, 'product_id' => $prod2->id, 'quantity' => 1, 'sort_order' => 2]);
        ProductBundleItem::create(['bundle_id' => $bundle->id, 'product_id' => $prod1->id, 'quantity' => 1, 'sort_order' => 1]);

        $bundle->load('items');
        $this->assertEquals($prod1->id, $bundle->items->first()->product_id);
    }
}
