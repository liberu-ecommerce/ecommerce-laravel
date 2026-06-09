<?php

namespace Tests\Unit;

use App\Models\Currency;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CurrencyModelTest extends TestCase
{
    use RefreshDatabase;

    private function makeCurrency(array $overrides = []): Currency
    {
        return Currency::create(array_merge([
            'code' => 'USD',
            'name' => 'US Dollar',
            'symbol' => '$',
            'symbol_position' => 'before',
            'decimal_places' => 2,
            'thousand_separator' => ',',
            'decimal_separator' => '.',
            'exchange_rate' => 1.0,
            'is_default' => false,
            'is_active' => true,
        ], $overrides));
    }

    public function test_format_price_with_symbol_before(): void
    {
        $currency = $this->makeCurrency([
            'code' => 'USD2',
            'symbol' => '$',
            'symbol_position' => 'before',
        ]);

        $this->assertEquals('$10.00', $currency->formatPrice(10.0));
    }

    public function test_format_price_with_symbol_after(): void
    {
        $currency = $this->makeCurrency([
            'code' => 'EUR',
            'symbol' => '€',
            'symbol_position' => 'after',
        ]);

        $this->assertEquals('10.00€', $currency->formatPrice(10.0));
    }

    public function test_convert_from_base(): void
    {
        $currency = $this->makeCurrency(['code' => 'GBP', 'exchange_rate' => 0.8]);

        $result = $currency->convertFromBase(100.0);

        $this->assertEquals(80.0, $result);
    }

    public function test_convert_to_base(): void
    {
        $currency = $this->makeCurrency(['code' => 'EUR2', 'exchange_rate' => 0.9]);

        $result = $currency->convertToBase(90.0);

        $this->assertEqualsWithDelta(100.0, $result, 0.01);
    }

    public function test_convert_to_base_returns_zero_for_zero_rate(): void
    {
        $currency = $this->makeCurrency(['code' => 'ZZZ', 'exchange_rate' => 0.0]);

        $result = $currency->convertToBase(100.0);

        $this->assertEquals(0.0, $result);
    }

    public function test_get_default_returns_default_currency(): void
    {
        $default = $this->makeCurrency(['code' => 'DEF', 'is_default' => true, 'is_active' => true]);
        $other = $this->makeCurrency(['code' => 'OTH', 'is_default' => false]);

        $result = Currency::getDefault();

        $this->assertNotNull($result);
        $this->assertEquals('DEF', $result->code);
    }

    public function test_get_default_returns_null_when_no_default(): void
    {
        $this->makeCurrency(['code' => 'AAA', 'is_default' => false]);

        $result = Currency::getDefault();

        $this->assertNull($result);
    }

    public function test_get_active_returns_only_active(): void
    {
        $this->makeCurrency(['code' => 'ACT', 'is_active' => true]);
        $this->makeCurrency(['code' => 'INA', 'is_active' => false]);

        $result = Currency::getActive();

        $codes = $result->pluck('code')->toArray();
        $this->assertContains('ACT', $codes);
        $this->assertNotContains('INA', $codes);
    }

    public function test_format_price_uses_thousand_separator(): void
    {
        $currency = $this->makeCurrency([
            'code' => 'BIG',
            'thousand_separator' => ',',
            'decimal_separator' => '.',
            'symbol' => '$',
            'symbol_position' => 'before',
            'decimal_places' => 2,
        ]);

        $this->assertEquals('$1,000.00', $currency->formatPrice(1000.0));
    }
}
