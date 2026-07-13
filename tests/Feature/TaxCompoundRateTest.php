<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\TaxClass;
use App\Models\TaxRate;
use App\Services\TaxCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * A compound tax rate is tax-on-tax: it applies to the item subtotal PLUS the
 * simple taxes, not the bare subtotal. calculateCartTax used to compute every rate
 * on the bare base, silently treating compound rates as simple (under-charge).
 */
class TaxCompoundRateTest extends TestCase
{
    use RefreshDatabase;

    private function rate(int $classId, float $rate, bool $compound, string $name): void
    {
        TaxRate::create([
            'tax_class_id' => $classId, 'country' => 'CA', 'rate' => $rate,
            'name' => $name, 'compound' => $compound, 'is_active' => true,
        ]);
    }

    public function test_compound_rate_is_taxed_on_base_plus_simple_tax(): void
    {
        $class = TaxClass::create(['name' => 'Standard', 'slug' => 'standard', 'is_active' => true]);
        $this->rate($class->id, 10, false, 'GST');  // simple 10%
        $this->rate($class->id, 5, true, 'PST');     // compound 5%

        $product = Product::factory()->create(['tax_class_id' => $class->id]);

        $result = app(TaxCalculator::class)->calculateCartTax(
            [['product' => $product, 'quantity' => 1, 'price' => 100]],
            ['country' => 'CA'],
            0
        );

        // simple: 10% of 100 = 10; compound: 5% of (100 + 10) = 5.50; total = 15.50 (not 15).
        $this->assertEquals(15.50, $result['total']);
    }

    public function test_simple_only_rates_are_unchanged(): void
    {
        $class = TaxClass::create(['name' => 'Standard', 'slug' => 'standard', 'is_active' => true]);
        $this->rate($class->id, 10, false, 'A');
        $this->rate($class->id, 5, false, 'B');

        $product = Product::factory()->create(['tax_class_id' => $class->id]);

        $result = app(TaxCalculator::class)->calculateCartTax(
            [['product' => $product, 'quantity' => 1, 'price' => 100]],
            ['country' => 'CA'],
            0
        );

        // Both simple on the base: 10 + 5 = 15.
        $this->assertEquals(15.0, $result['total']);
    }
}
