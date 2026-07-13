<?php

namespace Tests\Feature;

use App\Models\TaxClass;
use App\Models\TaxRate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Location matching must treat a rate's NULL location columns as wildcards and let
 * the most specific match win. The old rigid exact->state->country tiers made a
 * partially-specified rate (e.g. state + city, no ZIP) unreachable, so it fell
 * through to a coarser (wrong) rate.
 */
class TaxRateLocationMatchingTest extends TestCase
{
    use RefreshDatabase;

    private int $classId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->classId = TaxClass::create(['name' => 'Standard', 'slug' => 'standard', 'is_active' => true])->id;
    }

    private function rate(array $o): TaxRate
    {
        return TaxRate::create(array_merge([
            'tax_class_id' => $this->classId, 'country' => 'US', 'rate' => 5, 'name' => 'r', 'is_active' => true,
        ], $o));
    }

    public function test_city_level_rate_with_null_zip_is_matched_and_wins(): void
    {
        $city = $this->rate(['state' => 'CA', 'city' => 'Los Angeles', 'rate' => 9.5, 'name' => 'LA City']);
        $this->rate(['name' => 'US National', 'rate' => 5]); // broader, must not win

        $rates = TaxRate::findMatchingRates('US', 'CA', 'Los Angeles', '90001', $this->classId);

        $this->assertTrue($rates->contains('id', $city->id), 'City rate must match despite a null zip_code');
        $this->assertFalse($rates->contains('name', 'US National'), 'Coarser rate must not apply when a more specific one matches');
        $this->assertEquals(1, $rates->count());
    }

    public function test_country_rate_applies_when_no_finer_rate_matches(): void
    {
        $us = $this->rate(['name' => 'US National', 'rate' => 5]);

        $rates = TaxRate::findMatchingRates('US', 'TX', 'Austin', '73301', $this->classId);

        $this->assertTrue($rates->contains('id', $us->id));
    }

    public function test_state_rate_beats_country_rate(): void
    {
        $state = $this->rate(['state' => 'CA', 'rate' => 7.25, 'name' => 'CA']);
        $this->rate(['name' => 'US National', 'rate' => 5]);

        $rates = TaxRate::findMatchingRates('US', 'CA', 'Los Angeles', '90001', $this->classId);

        $this->assertTrue($rates->contains('id', $state->id));
        $this->assertFalse($rates->contains('name', 'US National'));
    }

    public function test_rate_for_a_different_state_does_not_match(): void
    {
        $this->rate(['state' => 'NY', 'rate' => 8, 'name' => 'NY']);

        $rates = TaxRate::findMatchingRates('US', 'CA', null, null, $this->classId);

        $this->assertFalse($rates->contains('name', 'NY'));
    }
}
