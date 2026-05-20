<?php

namespace App\Services;

use App\Models\TaxRate;
use App\Models\Product;

class TaxCalculator
{
    /**
     * Calculate tax for cart items
     */
    public function calculateCartTax(array $items, array $shippingAddress, float $shippingCost = 0): array
    {
        $taxLines = [];
        $totalTax = 0;

        $country = $shippingAddress['country'] ?? null;
        $state = $shippingAddress['state'] ?? null;
        $city = $shippingAddress['city'] ?? null;
        $zipCode = $shippingAddress['postal_code'] ?? null;

        if (!$country) {
            return ['total' => 0, 'lines' => []];
        }

        // Calculate tax for each item
        foreach ($items as $item) {
            $product = $item['product'] ?? null;
            $quantity = $item['quantity'] ?? 1;
            $price = $item['price'] ?? 0;

            if (!$product || !($product instanceof Product)) {
                continue;
            }

            // Skip if product is not taxable
            if (!($product->tax_status ?? true)) {
                continue;
            }

            $taxClassId = $product->tax_class_id;
            $itemSubtotal = $price * $quantity;

            // Find matching tax rates
            $rates = TaxRate::findMatchingRates($country, $state, $city, $zipCode, $taxClassId);

            foreach ($rates as $rate) {
                $taxAmount = $rate->calculateTax($itemSubtotal);
                $totalTax += $taxAmount;

                // Group by rate for tax lines
                $rateKey = $rate->id;
                if (!isset($taxLines[$rateKey])) {
                    $taxLines[$rateKey] = [
                        'label' => $rate->name,
                        'rate' => $rate->rate,
                        'amount' => 0,
                        'compound' => $rate->compound,
                    ];
                }
                $taxLines[$rateKey]['amount'] += $taxAmount;
            }
        }

        // Calculate shipping tax if applicable
        if ($shippingCost > 0) {
            $shippingRates = TaxRate::findMatchingRates($country, $state, $city, $zipCode)
                ->where('shipping', true);

            foreach ($shippingRates as $rate) {
                $taxAmount = $rate->calculateTax($shippingCost);
                $totalTax += $taxAmount;

                $rateKey = $rate->id;
                if (!isset($taxLines[$rateKey])) {
                    $taxLines[$rateKey] = [
                        'label' => $rate->name . ' (Shipping)',
                        'rate' => $rate->rate,
                        'amount' => 0,
                        'compound' => $rate->compound,
                    ];
                }
                $taxLines[$rateKey]['amount'] += $taxAmount;
            }
        }

        return [
            'total' => round($totalTax, 2),
            'lines' => array_values($taxLines),
        ];
    }

    /**
     * Calculate tax for a single product
     */
    public function calculateProductTax(Product $product, float $price, array $location): float
    {
        if (!($product->tax_status ?? true)) {
            return 0;
        }

        $rates = TaxRate::findMatchingRates(
            $location['country'],
            $location['state'] ?? null,
            $location['city'] ?? null,
            $location['postal_code'] ?? null,
            $product->tax_class_id
        );

        $totalTax = 0;
        foreach ($rates as $rate) {
            $totalTax += $rate->calculateTax($price);
        }

        return round($totalTax, 2);
    }

    /**
     * Get tax-inclusive price
     */
    public function getPriceWithTax(float $price, Product $product, array $location): float
    {
        $tax = $this->calculateProductTax($product, $price, $location);
        return round($price + $tax, 2);
    }

    /**
     * Check if prices should be displayed with tax
     */
    public function shouldDisplayPricesWithTax(): bool
    {
        return config('ecommerce.display_prices_with_tax', false);
    }
}
