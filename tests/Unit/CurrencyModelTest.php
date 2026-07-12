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

    public function test_convert_to_base_returns_zero_for_negative_rate(): void
    {
        $currency = $this->makeCurrency(['code' => 'NEG', 'exchange_rate' => -0.5]);

        $this->assertEquals(0.0, $currency->convertToBase(100.0));
    }

    public function test_convert_to_base_rounds_to_base_currency_decimal_places(): void
    {
        // Base currency has 0 decimal places (e.g. JPY): a base amount must be whole.
        $this->makeCurrency([
            'code' => 'JPY',
            'decimal_places' => 0,
            'exchange_rate' => 1.0,
            'is_default' => true,
            'is_active' => true,
        ]);

        // 100 / 1.5 = 66.6667 -> must round to 67 in a 0-decimal base currency.
        $currency = $this->makeCurrency(['code' => 'FOO', 'exchange_rate' => 1.5]);

        $this->assertEquals(67.0, $currency->convertToBase(100.0));
    }

    public function test_convert_round_trip_preserves_amount(): void
    {
        // No default currency -> convertToBase falls back to 2 decimals.
        $currency = $this->makeCurrency(['code' => 'RTP', 'exchange_rate' => 0.8654, 'decimal_places' => 2]);

        $inTarget = $currency->convertFromBase(50.0);           // base -> target
        $backToBase = $currency->convertToBase($inTarget);       // target -> base

        $this->assertEqualsWithDelta(50.0, $backToBase, 0.01);
    }

    public function test_get_default_returns_default_currency(): void
    {
        $this->makeCurrency(['code' => 'DEF', 'is_default' => true, 'is_active' => true]);
        $this->makeCurrency(['code' => 'OTH', 'is_default' => false]);

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

    public function test_exchange_rate_holds_real_world_fx_values(): void
    {
        // IDR-class rate (>9999) overflows decimal(10,6) on MySQL; column must be widened.
        // SQLite ignores precision, so this only truly guards after the widening migration
        // runs on MySQL. It locks that exchange_rate stays decimal and round-trips.
        $currency = $this->makeCurrency(['code' => 'IDR', 'exchange_rate' => 16500.123456]);

        $this->assertEqualsWithDelta(16500.123456, (float) $currency->fresh()->exchange_rate, 0.000001);
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
