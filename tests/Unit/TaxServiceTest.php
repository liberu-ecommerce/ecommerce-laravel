<?php

namespace Tests\Unit;

use App\Models\TaxClass;
use App\Models\TaxRate;
use App\Services\TaxService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaxServiceTest extends TestCase
{
    use RefreshDatabase;

    private TaxService $service;
    private int $taxClassId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new TaxService();
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
            'name' => 'Standard Rate',
            'country' => 'US',
            'rate' => 10.00,
            'priority' => 1,
            'compound' => false,
            'shipping' => false,
            'is_active' => true,
        ], $overrides));
    }

    public function test_calculate_tax_for_cart_returns_zero_without_address(): void
    {
        $cart = [['price' => 100, 'quantity' => 1]];

        $tax = $this->service->calculateTaxForCart($cart, null);

        $this->assertEquals(0.0, $tax);
    }

    public function test_calculate_tax_for_cart_returns_zero_when_no_rates_match(): void
    {
        $cart = [['price' => 100, 'quantity' => 1]];

        $tax = $this->service->calculateTaxForCart($cart, '123 Main St, Springfield, CA 90210');

        $this->assertEquals(0.0, $tax);
    }

    public function test_calculate_tax_for_cart_applies_matching_rate(): void
    {
        $this->makeRate(['name' => 'CA Tax', 'state' => 'CA']);

        $cart = [['price' => 100, 'quantity' => 2]];

        $tax = $this->service->calculateTaxForCart($cart, '123 Main St, Springfield, CA 90210');

        $this->assertGreaterThan(0, $tax);
    }

    public function test_calculate_tax_returns_zero_for_unknown_country(): void
    {
        $tax = $this->service->calculateTax(100.0, 'ZZ');

        $this->assertEquals(0.0, $tax);
    }

    public function test_calculate_tax_applies_rate_for_known_country(): void
    {
        $this->makeRate(['rate' => 8.00, 'name' => 'US 8%']);

        $tax = $this->service->calculateTax(100.0, 'US');

        $this->assertEquals(8.00, $tax);
    }

    public function test_calculate_tax_scales_with_amount(): void
    {
        $this->makeRate(['rate' => 10.00]);

        $taxSingle = $this->service->calculateTax(100.0, 'US');
        $taxDouble = $this->service->calculateTax(200.0, 'US');

        $this->assertEquals($taxSingle * 2, $taxDouble);
    }

    public function test_get_tax_details_returns_empty_without_address(): void
    {
        $details = $this->service->getTaxDetails([], null);

        $this->assertEmpty($details);
    }

    public function test_get_tax_details_returns_rate_info(): void
    {
        $this->makeRate(['name' => 'State Tax', 'rate' => 5.00]);

        $cart = [['price' => 100, 'quantity' => 1]];
        $details = $this->service->getTaxDetails($cart, '123 Main St, US 10001');

        $this->assertNotEmpty($details);
        $this->assertArrayHasKey('name', $details[0]);
        $this->assertArrayHasKey('rate', $details[0]);
        $this->assertArrayHasKey('amount', $details[0]);
    }

    public function test_multiple_rates_are_summed(): void
    {
        $this->makeRate(['name' => 'Federal', 'rate' => 5.00, 'priority' => 1]);
        $this->makeRate(['name' => 'State', 'rate' => 3.00, 'priority' => 2]);

        $tax = $this->service->calculateTax(100.0, 'US');

        $this->assertEquals(8.00, $tax);
    }

    public function test_tax_for_cart_is_calculated_on_the_amount_after_discount(): void
    {
        $this->makeRate(['rate' => 10.00]);
        $cart = [['price' => 100, 'quantity' => 1]];

        // subtotal 100, discount 20 => taxable 80 => 10% = 8 (not 10 on the full subtotal)
        $tax = $this->service->calculateTaxForCart($cart, '123 Main St, CA 90210', 20);

        $this->assertEquals(8.00, $tax);
    }
}
