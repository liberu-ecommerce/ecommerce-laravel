<?php

namespace App\Support;

/**
 * EU member states and their STANDARD VAT rates (percent), used to seed the tax engine
 * and to scope the OSS/MOSS report to the EU.
 *
 * Rates are the standard rates as of 2025 — reduced/zero rates and per-product-class
 * nuances are out of scope here (the report aggregates whatever VAT was actually
 * charged on each order, so a later rate change or a manually-edited TaxRate flows
 * through without touching this list). Update a rate here + re-run the seeder to change
 * what new EU orders are taxed.
 */
final class EuVat
{
    /** @var array<string, float> ISO-3166-1 alpha-2 => standard VAT rate (%) */
    public const STANDARD_RATES = [
        'AT' => 20.0,   // Austria
        'BE' => 21.0,   // Belgium
        'BG' => 20.0,   // Bulgaria
        'HR' => 25.0,   // Croatia
        'CY' => 19.0,   // Cyprus
        'CZ' => 21.0,   // Czechia
        'DK' => 25.0,   // Denmark
        'EE' => 22.0,   // Estonia
        'FI' => 25.5,   // Finland
        'FR' => 20.0,   // France
        'DE' => 19.0,   // Germany
        'GR' => 24.0,   // Greece
        'HU' => 27.0,   // Hungary
        'IE' => 23.0,   // Ireland
        'IT' => 22.0,   // Italy
        'LV' => 21.0,   // Latvia
        'LT' => 21.0,   // Lithuania
        'LU' => 17.0,   // Luxembourg
        'MT' => 18.0,   // Malta
        'NL' => 21.0,   // Netherlands
        'PL' => 23.0,   // Poland
        'PT' => 23.0,   // Portugal
        'RO' => 19.0,   // Romania
        'SK' => 23.0,   // Slovakia
        'SI' => 22.0,   // Slovenia
        'ES' => 21.0,   // Spain
        'SE' => 25.0,   // Sweden
    ];

    /** @return string[] EU member-state ISO codes */
    public static function memberStates(): array
    {
        return array_keys(self::STANDARD_RATES);
    }

    public static function isMemberState(?string $country): bool
    {
        return $country !== null && array_key_exists(strtoupper($country), self::STANDARD_RATES);
    }

    public static function standardRate(string $country): ?float
    {
        return self::STANDARD_RATES[strtoupper($country)] ?? null;
    }
}
