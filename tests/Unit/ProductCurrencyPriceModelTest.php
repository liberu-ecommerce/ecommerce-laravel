<?php

namespace Tests\Unit;

use App\Models\Currency;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductCurrencyPrice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductCurrencyPriceModelTest extends TestCase
{
    use RefreshDatabase;

    private function makeProduct(): Product
    {
        $cat = ProductCategory::create(['name' => 'PCP Cat', 'slug' => 'pcp-cat-' . uniqid()]);
        return Product::create([
            'name' => 'PCP Product',
            'slug' => 'pcp-prod-' . uniqid(),
            'price' => 100.00,
            'category_id' => $cat->id,
            'inventory_count' => 5,
        ]);
    }

    private function makeCurrency(string $code): Currency
    {
        return Currency::create([
            'name' => "Currency $code",
            'code' => $code,
            'symbol' => $code[0],
            'exchange_rate' => 1.0,
            'is_default' => false,
            'is_active' => true,
        ]);
    }

    public function test_product_currency_price_can_be_created(): void
    {
        $product = $this->makeProduct();
        $this->makeCurrency('EUR');

        $price = ProductCurrencyPrice::create([
            'product_id' => $product->id,
            'currency_code' => 'EUR',
            'price' => 85.00,
        ]);

        $this->assertInstanceOf(ProductCurrencyPrice::class, $price);
        $this->assertEquals('EUR', $price->currency_code);
    }

    public function test_price_is_decimal_cast(): void
    {
        $product = $this->makeProduct();
        $this->makeCurrency('GBP');

        $price = ProductCurrencyPrice::create([
            'product_id' => $product->id,
            'currency_code' => 'GBP',
            'price' => 79.99,
            'compare_at_price' => 99.99,
        ]);

        $fresh = $price->fresh();
        $this->assertEquals('79.99', $fresh->price);
        $this->assertEquals('99.99', $fresh->compare_at_price);
    }

    public function test_belongs_to_product(): void
    {
        $product = $this->makeProduct();
        $this->makeCurrency('JPY');

        $price = ProductCurrencyPrice::create([
            'product_id' => $product->id,
            'currency_code' => 'JPY',
            'price' => 12000,
        ]);

        $this->assertInstanceOf(Product::class, $price->product);
        $this->assertEquals($product->id, $price->product->id);
    }
}
