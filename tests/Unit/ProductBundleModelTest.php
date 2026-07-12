<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Models\ProductBundle;
use App\Models\ProductBundleItem;
use App\Models\ProductCategory;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductBundleModelTest extends TestCase
{
    use RefreshDatabase;

    private function makeProduct(float $price = 30.00): Product
    {
        $category = ProductCategory::create([
            'name' => 'Bundle Cat',
            'slug' => 'bundle-cat-' . uniqid(),
        ]);
        return Product::create([
            'name' => 'Bundle Product',
            'slug' => 'bundle-prod-' . uniqid(),
            'price' => $price,
            'category_id' => $category->id,
            'inventory_count' => 10,
        ]);
    }

    private function makeBundle(Product $product, array $overrides = []): ProductBundle
    {
        return ProductBundle::create(array_merge([
            'product_id' => $product->id,
            'name' => 'Test Bundle',
            'discount_amount' => 0,
            'discount_percentage' => 0,
            'is_active' => true,
        ], $overrides));
    }

    private function addBundleItem(ProductBundle $bundle, Product $product, int $qty = 1): ProductBundleItem
    {
        return ProductBundleItem::create([
            'bundle_id' => $bundle->id,
            'product_id' => $product->id,
            'quantity' => $qty,
            'sort_order' => 1,
        ]);
    }

    public function test_get_bundle_price_with_percentage_discount(): void
    {
        $mainProduct = $this->makeProduct();
        $item1 = $this->makeProduct(50.00);
        $item2 = $this->makeProduct(30.00);

        $bundle = $this->makeBundle($mainProduct, ['discount_percentage' => 10]);
        $this->addBundleItem($bundle, $item1, 1);
        $this->addBundleItem($bundle, $item2, 1);

        $bundle->load('items.product');

        // Regular price = 50 + 30 = 80. With 10% off = 72
        $this->assertEqualsWithDelta(72.0, $bundle->getBundlePrice(), 0.01);
    }

    public function test_get_bundle_price_with_flat_discount(): void
    {
        $mainProduct = $this->makeProduct();
        $item = $this->makeProduct(100.00);

        $bundle = $this->makeBundle($mainProduct, ['discount_amount' => 15]);
        $this->addBundleItem($bundle, $item, 1);

        $bundle->load('items.product');

        $this->assertEqualsWithDelta(85.0, $bundle->getBundlePrice(), 0.01);
    }

    public function test_get_regular_price_sums_items(): void
    {
        $mainProduct = $this->makeProduct();
        $item1 = $this->makeProduct(20.00);
        $item2 = $this->makeProduct(30.00);

        $bundle = $this->makeBundle($mainProduct);
        $this->addBundleItem($bundle, $item1, 2);
        $this->addBundleItem($bundle, $item2, 1);

        $bundle->load('items.product');

        // 20*2 + 30*1 = 70
        $this->assertEqualsWithDelta(70.0, $bundle->getRegularPrice(), 0.01);
    }

    public function test_get_savings(): void
    {
        $mainProduct = $this->makeProduct();
        $item = $this->makeProduct(100.00);

        $bundle = $this->makeBundle($mainProduct, ['discount_amount' => 20]);
        $this->addBundleItem($bundle, $item, 1);

        $bundle->load('items.product');

        $this->assertEqualsWithDelta(20.0, $bundle->getSavings(), 0.01);
    }

    public function test_is_active_cast_to_boolean(): void
    {
        $product = $this->makeProduct();
        $bundle = $this->makeBundle($product, ['is_active' => true]);

        $this->assertIsBool($bundle->fresh()->is_active);
    }

    public function test_belongs_to_product(): void
    {
        $product = $this->makeProduct();
        $bundle = $this->makeBundle($product);

        $this->assertInstanceOf(Product::class, $bundle->product);
    }

    public function test_is_in_stock_true_when_variant_item_has_inventory(): void
    {
        $mainProduct = $this->makeProduct();
        $variantProduct = $this->makeProduct();
        $variant = ProductVariant::create([
            'product_id' => $variantProduct->id,
            'sku' => 'STOCK-' . uniqid(),
            'price' => 10.00,
            'inventory_quantity' => 5,
        ]);

        $bundle = $this->makeBundle($mainProduct);
        ProductBundleItem::create([
            'bundle_id' => $bundle->id,
            'product_id' => $variantProduct->id,
            'product_variant_id' => $variant->id,
            'quantity' => 2,
            'sort_order' => 1,
        ]);

        $bundle->load('items.variant', 'items.product');

        // variant stock (5) >= qty (2) -> in stock. Regression: was reading the
        // non-existent inventory_count column on the variant and always failing.
        $this->assertTrue($bundle->isInStock());
    }

    public function test_is_in_stock_false_when_variant_item_out_of_stock(): void
    {
        $mainProduct = $this->makeProduct();
        $variantProduct = $this->makeProduct();
        $variant = ProductVariant::create([
            'product_id' => $variantProduct->id,
            'sku' => 'STOCK-' . uniqid(),
            'price' => 10.00,
            'inventory_quantity' => 1,
        ]);

        $bundle = $this->makeBundle($mainProduct);
        ProductBundleItem::create([
            'bundle_id' => $bundle->id,
            'product_id' => $variantProduct->id,
            'product_variant_id' => $variant->id,
            'quantity' => 3,
            'sort_order' => 1,
        ]);

        $bundle->load('items.variant', 'items.product');

        // variant stock (1) < qty (3) -> out of stock.
        $this->assertFalse($bundle->isInStock());
    }

    public function test_bundle_price_floored_at_zero_when_discount_over_100_percent(): void
    {
        $mainProduct = $this->makeProduct();
        $item = $this->makeProduct(40.00);

        $bundle = $this->makeBundle($mainProduct, ['discount_percentage' => 150]);
        $this->addBundleItem($bundle, $item, 1);

        $bundle->load('items.product');

        // A >100% discount must never produce a negative price.
        $this->assertEqualsWithDelta(0.0, $bundle->getBundlePrice(), 0.01);
        $this->assertGreaterThanOrEqual(0.0, $bundle->getBundlePrice());
    }
}
