<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Models\TaxClass;
use App\Models\TaxRate;
use App\Models\ProductCategory;
use App\Services\TaxCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaxCalculatorTest extends TestCase
{
    use RefreshDatabase;

    private TaxCalculator $calculator;
    private int $taxClassId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calculator = new TaxCalculator();
        $this->taxClassId = TaxClass::create([
            'name' => 'Standard',
            'slug' => 'standard',
            'is_active' => true,
        ])->id;
    }

    private function makeProduct(array $overrides = []): Product
    {
        $category = ProductCategory::create([
            'name' => 'Test Category',
            'slug' => 'test-category-' . uniqid(),
        ]);

        return Product::create(array_merge([
            'name' => 'Test Product',
            'slug' => 'test-product-' . uniqid(),
            'price' => 100.00,
            'category_id' => $category->id,
            'inventory_count' => 10,
        ], $overrides));
    }

    private function makeRate(array $overrides = []): TaxRate
    {
        return TaxRate::create(array_merge([
            'tax_class_id' => $this->taxClassId,
            'name' => 'Standard Tax',
            'country' => 'US',
            'rate' => 10.00,
            'priority' => 1,
            'compound' => false,
            'shipping' => false,
            'is_active' => true,
        ], $overrides));
    }

    public function test_calculate_cart_tax_returns_zero_without_country(): void
    {
        $items = [];
        $address = [];

        $result = $this->calculator->calculateCartTax($items, $address);

        $this->assertEquals(['total' => 0, 'lines' => []], $result);
    }

    public function test_calculate_cart_tax_with_taxable_product(): void
    {
        $this->makeRate(['country' => 'US', 'rate' => 10.00]);
        $product = $this->makeProduct();

        $items = [
            ['product' => $product, 'price' => 100.00, 'quantity' => 1],
        ];
        $address = ['country' => 'US'];

        $result = $this->calculator->calculateCartTax($items, $address);

        $this->assertEquals(10.00, $result['total']);
        $this->assertCount(1, $result['lines']);
    }

    public function test_calculate_cart_tax_skips_non_product_items(): void
    {
        $this->makeRate(['country' => 'US', 'rate' => 10.00]);

        $items = [
            ['product' => null, 'price' => 100.00, 'quantity' => 1],
        ];
        $address = ['country' => 'US'];

        $result = $this->calculator->calculateCartTax($items, $address);

        $this->assertEquals(0.0, $result['total']);
    }

    public function test_calculate_product_tax_returns_correct_amount(): void
    {
        $this->makeRate(['country' => 'US', 'rate' => 8.00]);
        $product = $this->makeProduct();

        $tax = $this->calculator->calculateProductTax($product, 100.00, ['country' => 'US']);

        $this->assertEquals(8.00, $tax);
    }

    public function test_calculate_product_tax_returns_zero_for_unknown_location(): void
    {
        $product = $this->makeProduct();

        $tax = $this->calculator->calculateProductTax($product, 100.00, ['country' => 'XX']);

        $this->assertEquals(0.0, $tax);
    }

    public function test_get_price_with_tax_adds_tax_to_price(): void
    {
        $this->makeRate(['country' => 'US', 'rate' => 10.00]);
        $product = $this->makeProduct();

        $priceWithTax = $this->calculator->getPriceWithTax(100.00, $product, ['country' => 'US']);

        $this->assertEquals(110.00, $priceWithTax);
    }

    public function test_should_display_prices_with_tax_returns_bool(): void
    {
        $result = $this->calculator->shouldDisplayPricesWithTax();

        $this->assertIsBool($result);
    }

    public function test_calculate_cart_tax_includes_shipping_tax(): void
    {
        $this->makeRate(['country' => 'US', 'rate' => 5.00, 'shipping' => true, 'name' => 'Shipping Tax']);
        $product = $this->makeProduct();

        $items = [
            ['product' => $product, 'price' => 100.00, 'quantity' => 1],
        ];
        $address = ['country' => 'US'];

        $result = $this->calculator->calculateCartTax($items, $address, 20.00);

        // 5% on 100 (product) + 5% on 20 (shipping) = 5 + 1 = 6
        $this->assertEquals(6.00, $result['total']);
    }
}
