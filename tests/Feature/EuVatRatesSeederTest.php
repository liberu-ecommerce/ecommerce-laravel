<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\TaxRate;
use App\Services\TaxCalculator;
use App\Support\EuVat;
use Database\Seeders\EuVatRatesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EuVatRatesSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeds_a_rate_for_every_member_state(): void
    {
        $this->seed(EuVatRatesSeeder::class);

        $this->assertSame(27, TaxRate::whereIn('country', EuVat::memberStates())->count());
        $this->assertDatabaseHas('tax_rates', ['country' => 'DE', 'rate' => 19.0]);
    }

    public function test_is_idempotent(): void
    {
        $this->seed(EuVatRatesSeeder::class);
        $this->seed(EuVatRatesSeeder::class);

        $this->assertSame(27, TaxRate::whereIn('country', EuVat::memberStates())->count());
    }

    public function test_a_german_order_is_taxed_at_the_seeded_destination_rate(): void
    {
        $this->seed(EuVatRatesSeeder::class);
        $product = Product::factory()->create(['price' => 100, 'tax_class_id' => null]);

        $tax = app(TaxCalculator::class)->calculateCartTax(
            [['product' => $product, 'quantity' => 1, 'price' => 100.0]],
            ['country' => 'DE'],
        );

        // 19% German VAT on €100.
        $this->assertSame(19.0, $tax['total']);
    }
}
