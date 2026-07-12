<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\WholesaleGroup;
use App\Models\WholesalePriceTier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WholesalePriceTierModelTest extends TestCase
{
    use RefreshDatabase;

    private Product $product;
    private WholesaleGroup $group;

    protected function setUp(): void
    {
        parent::setUp();
        $cat = ProductCategory::create(['name' => 'WS Cat', 'slug' => 'ws-cat-' . uniqid()]);
        $this->product = Product::create([
            'name' => 'WS Product',
            'slug' => 'ws-prod-' . uniqid(),
            'price' => 50.00,
            'category_id' => $cat->id,
            'inventory_count' => 100,
        ]);
        $this->group = WholesaleGroup::create([
            'name' => 'Resellers',
            'discount_percentage' => 20.00,
            'is_active' => true,
        ]);
    }

    private function makeTier(array $overrides = []): WholesalePriceTier
    {
        return WholesalePriceTier::create(array_merge([
            'product_id' => $this->product->id,
            'wholesale_group_id' => $this->group->id,
            'min_quantity' => 10,
            'price' => 40.00,
        ], $overrides));
    }

    public function test_wholesale_price_tier_can_be_created(): void
    {
        $tier = $this->makeTier();

        $this->assertInstanceOf(WholesalePriceTier::class, $tier);
        $this->assertEquals(10, $tier->min_quantity);
    }

    public function test_get_price_for_quantity_returns_matching_tier(): void
    {
        $this->makeTier(['min_quantity' => 10, 'price' => 40.00]);
        $this->makeTier(['min_quantity' => 50, 'price' => 35.00]);

        $price = WholesalePriceTier::getPriceForQuantity($this->product->id, 20);

        $this->assertEquals(40.0, $price);
    }

    public function test_get_price_for_quantity_returns_null_when_no_tier(): void
    {
        $price = WholesalePriceTier::getPriceForQuantity($this->product->id, 1);

        $this->assertNull($price);
    }

    public function test_get_price_for_quantity_uses_highest_qualifying_tier(): void
    {
        $this->makeTier(['min_quantity' => 10, 'price' => 40.00]);
        $this->makeTier(['min_quantity' => 100, 'price' => 30.00]);

        $price = WholesalePriceTier::getPriceForQuantity($this->product->id, 150);

        $this->assertEquals(30.0, $price);
    }

    public function test_belongs_to_product(): void
    {
        $tier = $this->makeTier();

        $this->assertInstanceOf(Product::class, $tier->product);
        $this->assertEquals($this->product->id, $tier->product->id);
    }

    public function test_overlapping_tiers_return_cheapest_qualifying_price(): void
    {
        // A wide promo tier (10-100 @ 30) overlaps a bulk tier (50+ @ 35).
        // At qty 60 both ranges qualify; the customer must get the cheaper 30,
        // not the higher-min (pricier) tier.
        $this->makeTier(['min_quantity' => 10, 'max_quantity' => 100, 'price' => 30.00]);
        $this->makeTier(['min_quantity' => 50, 'max_quantity' => null, 'price' => 35.00]);

        $price = WholesalePriceTier::getPriceForQuantity($this->product->id, 60);

        $this->assertEquals(30.0, $price);
    }

    public function test_max_quantity_boundary_is_inclusive_and_non_overlapping(): void
    {
        $this->makeTier(['min_quantity' => 10, 'max_quantity' => 49, 'price' => 40.00]);
        $this->makeTier(['min_quantity' => 50, 'max_quantity' => null, 'price' => 35.00]);

        $this->assertEquals(40.0, WholesalePriceTier::getPriceForQuantity($this->product->id, 49));
        $this->assertEquals(35.0, WholesalePriceTier::getPriceForQuantity($this->product->id, 50));
    }

    public function test_percentage_tier_computes_discount_off_retail_price(): void
    {
        // product retail price is 50.00 (see setUp). A 10% percentage tier must
        // yield 45.00 regardless of the fixed `price` column value.
        $this->makeTier([
            'min_quantity' => 10,
            'price' => 99.00,
            'discount_percentage' => 10.00,
        ]);

        $price = WholesalePriceTier::getPriceForQuantity($this->product->id, 20);

        $this->assertEquals(45.0, $price);
    }

    public function test_fixed_price_tier_ignores_null_discount_percentage(): void
    {
        $this->makeTier([
            'min_quantity' => 10,
            'price' => 42.00,
            'discount_percentage' => null,
        ]);

        $this->assertEquals(42.0, WholesalePriceTier::getPriceForQuantity($this->product->id, 20));
    }
}
