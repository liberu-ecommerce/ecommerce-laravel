<?php

namespace Database\Seeders;

use App\Models\TaxClass;
use App\Models\TaxRate;
use App\Support\EuVat;
use Illuminate\Database\Seeder;

/**
 * Seed the standard VAT rate for every EU member state into the destination-based tax
 * engine, so an EU B2C order is taxed at the buyer-country rate. Idempotent — safe to
 * re-run after a rate change (updateOrCreate keyed on the rate's country + name).
 */
class EuVatRatesSeeder extends Seeder
{
    public function run(): void
    {
        $taxClass = TaxClass::firstOrCreate(
            ['slug' => 'standard-rate'],
            ['name' => 'Standard Rate', 'is_active' => true],
        );

        foreach (EuVat::STANDARD_RATES as $country => $rate) {
            TaxRate::updateOrCreate(
                ['country' => $country, 'name' => "EU VAT {$country}", 'tax_class_id' => $taxClass->id],
                [
                    'state' => null,
                    'city' => null,
                    'zip_code' => null,
                    'rate' => $rate,
                    'priority' => 0,
                    'compound' => false,
                    'shipping' => true,
                    'is_active' => true,
                ],
            );
        }
    }
}
