<?php

namespace App\Services;

use App\Models\TaxRate;

class TaxService
{
    /**
     * Calculate tax for a cart based on shipping address
     */
    public function calculateTaxForCart(array $cart, ?string $shippingAddress = null): float
    {
        if (!$shippingAddress) {
            return 0;
        }

        // Parse address to extract location information
        $location = $this->parseAddress($shippingAddress);
        
        // Get applicable tax rates
        $taxRates = TaxRate::findMatchingRates(
            $location['country'] ?? 'US',
            $location['state'] ?? null,
            $location['city'] ?? null,
            $location['zip'] ?? null
        );

        if ($taxRates->isEmpty()) {
            return 0;
        }

        // Calculate subtotal
        $subtotal = collect($cart)->sum(function ($item) {
            return $item['price'] * $item['quantity'];
        });

        // Calculate tax
        $totalTax = 0;
        foreach ($taxRates as $rate) {
            $totalTax += $rate->calculateTax($subtotal);
        }

        return round($totalTax, 2);
    }

    /**
     * Calculate tax for a given amount and location
     */
    public function calculateTax(float $amount, string $country, ?string $state = null, ?string $city = null, ?string $zipCode = null): float
    {
        $taxRates = TaxRate::findMatchingRates($country, $state, $city, $zipCode);

        if ($taxRates->isEmpty()) {
            return 0;
        }

        $totalTax = 0;
        foreach ($taxRates as $rate) {
            $totalTax += $rate->calculateTax($amount);
        }

        return round($totalTax, 2);
    }

    /**
     * Parse address string to extract location components
     * This is a simple implementation - could be improved with geocoding API
     */
    private function parseAddress(string $address): array
    {
        $location = [
            'country' => 'US', // Default to US
            'state' => null,
            'city' => null,
            'zip' => null,
        ];

        // Try to extract ZIP code (5 digits or 5+4 format)
        if (preg_match('/\b(\d{5}(?:-\d{4})?)\b/', $address, $matches)) {
            $location['zip'] = $matches[1];
        }

        // Try to extract 2-letter state code
        if (preg_match('/\b([A-Z]{2})\b/', $address, $matches)) {
            $location['state'] = $matches[1];
        }

        return $location;
    }

    /**
     * Get tax rate details for display
     */
    public function getTaxDetails(array $cart, ?string $shippingAddress = null): array
    {
        if (!$shippingAddress) {
            return [];
        }

        $location = $this->parseAddress($shippingAddress);
        $taxRates = TaxRate::findMatchingRates(
            $location['country'] ?? 'US',
            $location['state'] ?? null,
            $location['city'] ?? null,
            $location['zip'] ?? null
        );

        $subtotal = collect($cart)->sum(function ($item) {
            return $item['price'] * $item['quantity'];
        });

        $details = [];
        foreach ($taxRates as $rate) {
            $details[] = [
                'name' => $rate->name,
                'rate' => $rate->rate,
                'amount' => $rate->calculateTax($subtotal),
            ];
        }

        return $details;
    }
}
