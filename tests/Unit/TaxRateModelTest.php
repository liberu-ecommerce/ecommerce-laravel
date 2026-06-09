<?php

namespace Tests\Unit;

use App\Models\TaxClass;
use App\Models\TaxRate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaxRateModelTest extends TestCase
{
    use RefreshDatabase;

    private int $taxClassId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->taxClassId = TaxClass::create([
            'name' => 'Standard',
            'slug' => 'standard',
            'is_active' => true,
        ])->id;
    }

    private function makeRate(array $overrides = []): TaxRate
    {
        return TaxRate::create(array_merge([
            'tax_class_id' => $this->taxClassId,
            'name' => 'US Standard',
            'country' => 'US',
            'rate' => 10.00,
            'priority' => 1,
            'compound' => false,
            'shipping' => false,
            'is_active' => true,
        ], $overrides));
    }

    public function test_calculate_tax_returns_correct_amount(): void
    {
        $rate = $this->makeRate(['rate' => 10.00]);

        $tax = $rate->calculateTax(100.00);

        $this->assertEquals(10.00, $tax);
    }

    public function test_calculate_tax_rounds_to_two_decimals(): void
    {
        $rate = $this->makeRate(['rate' => 7.5]);

        $tax = $rate->calculateTax(99.99);

        $this->assertEquals(7.50, $tax);
    }

    public function test_calculate_tax_on_zero_returns_zero(): void
    {
        $rate = $this->makeRate(['rate' => 10.00]);

        $this->assertEquals(0.00, $rate->calculateTax(0));
    }

    public function test_find_matching_rates_by_country(): void
    {
        $us = $this->makeRate(['country' => 'US', 'name' => 'US Tax']);
        $ca = $this->makeRate(['country' => 'CA', 'name' => 'CA Tax']);

        $rates = TaxRate::findMatchingRates('US');

        $this->assertTrue($rates->contains('id', $us->id));
        $this->assertFalse($rates->contains('id', $ca->id));
    }

    public function test_find_matching_rates_excludes_inactive(): void
    {
        $active = $this->makeRate(['name' => 'Active Rate']);
        $inactive = $this->makeRate(['name' => 'Inactive Rate', 'is_active' => false]);

        $rates = TaxRate::findMatchingRates('US');

        $this->assertTrue($rates->contains('id', $active->id));
        $this->assertFalse($rates->contains('id', $inactive->id));
    }

    public function test_find_matching_rates_by_state(): void
    {
        $stateRate = $this->makeRate(['name' => 'CA State', 'state' => 'CA']);
        $nationalRate = $this->makeRate(['name' => 'US National']);

        $rates = TaxRate::findMatchingRates('US', 'CA');

        // State-specific match should be found
        $this->assertTrue($rates->contains('id', $stateRate->id));
    }

    public function test_find_matching_rates_returns_empty_for_unknown_country(): void
    {
        $this->makeRate(['country' => 'US']);

        $rates = TaxRate::findMatchingRates('XX');

        $this->assertTrue($rates->isEmpty());
    }
}
