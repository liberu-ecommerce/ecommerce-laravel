<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\TaxClass;
use App\Models\TaxRate;
use App\Services\TaxCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * A product with no tax class (tax_class_id is null — the default, since the column
 * isn't fillable) must be taxed by ONE tax class, not by every class's rate summed.
 * The old findMatchingRates skipped the class filter for a null class and returned
 * (and calculateCartTax summed) the rates of every class → silent over-charge.
 */
class TaxClasslessProductTest extends TestCase
{
    use RefreshDatabase;

    private function usClass(string $name, float $rate): TaxClass
    {
        $class = TaxClass::create(['name' => $name, 'slug' => strtolower($name), 'is_active' => true]);
        TaxRate::create([
            'tax_class_id' => $class->id, 'country' => 'US', 'rate' => $rate,
            'name' => "US {$name}", 'is_active' => true,
        ]);

        return $class;
    }

    public function test_classless_product_is_not_taxed_by_every_tax_class(): void
    {
        $this->usClass('Standard', 8);   // first active class -> the fallback
        $this->usClass('Reduced', 5);

        $product = Product::factory()->create(); // tax_class_id defaults to null
        $this->assertNull($product->tax_class_id);

        $result = app(TaxCalculator::class)->calculateCartTax(
            [['product' => $product, 'quantity' => 1, 'price' => 100]],
            ['country' => 'US'],
            0
        );

        // Standard 8% only — NOT 8% + 5% = 13.
        $this->assertEquals(8.0, $result['total']);
    }

    public function test_single_tax_class_still_taxes_a_classless_product(): void
    {
        $this->usClass('Standard', 8);

        $product = Product::factory()->create();

        $result = app(TaxCalculator::class)->calculateCartTax(
            [['product' => $product, 'quantity' => 1, 'price' => 100]],
            ['country' => 'US'],
            0
        );

        $this->assertEquals(8.0, $result['total']);
    }
}
