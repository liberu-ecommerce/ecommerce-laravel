<?php

namespace App\Services;

use App\Models\ShippingMethod;
use Illuminate\Support\Facades\Http;

class ShippingService
{
    public function getAvailableShippingMethods($cart = null, $address = null)
    {
        // Only offer active methods; a deactivated method must never be selectable.
        $availableMethods = ShippingMethod::where('is_active', true)->get();

        if (!$cart || !$address) {
            return $availableMethods;
        }

        // Filter methods based on package weight, dimensions, and destination
        return $availableMethods->filter(function ($method) use ($cart, $address) {
            return $this->isMethodAvailable($method, $cart, $address);
        });
    }

    public function calculateShippingCost(ShippingMethod $method, $cart, $address)
    {
        // Implement logic to calculate shipping cost based on the method, cart contents, and address
        $baseRate = $method->base_rate;
        $weightRate = $this->calculateWeightRate($method, $cart);
        $distanceRate = $this->calculateDistanceRate($method, $address);

        // Round to cents: float weight math otherwise leaks e.g. 0.30000000000000004.
        return round($baseRate + $weightRate + $distanceRate, 2);
    }

    public function verifyAddress($address)
    {
        // Implement address verification logic
        // This is a placeholder implementation. In a real-world scenario, you would integrate with an address verification API.
        $response = Http::get('https://api.address-verifier.com', [
            'address' => $address,
            'api_key' => config('services.address_verifier.api_key'),
        ]);

        if ($response->successful()) {
            return $response->json();
        }

        return null;
    }

    private function isMethodAvailable($method, $cart, $address)
    {
        // Implement logic to check if the shipping method is available for the given cart and address
        // This is a simplified example. You would need to implement more complex logic based on your specific requirements.
        $totalWeight = $this->calculateTotalWeight($cart);
        $maxWeight = $method->max_weight;

        // Null max_weight = no weight limit. Without this guard, `$w <= null`
        // evaluates as `$w <= 0` in PHP, wrongly rejecting unlimited methods.
        return $maxWeight === null || $totalWeight <= $maxWeight;
    }

    private function calculateWeightRate($method, $cart)
    {
        $totalWeight = $this->calculateTotalWeight($cart);
        return $totalWeight * $method->weight_rate;
    }

    private function calculateDistanceRate($method, $address)
    {
        // Implement logic to calculate distance-based rate
        // This is a placeholder. You would need to integrate with a distance calculation service.
        return 0;
    }

    private function calculateTotalWeight($cart)
    {
        return array_sum(array_map(function ($item) {
            // ponytail: session cart items carry no 'weight' yet (wiring gap in
            // Cart/CheckoutController) — treat missing weight as 0, don't fatal.
            return ($item['weight'] ?? 0) * $item['quantity'];
        }, $cart));
    }

    public function calculateDropShippingCost(ShippingMethod $method, $cart, $address)
    {
        // Add a small premium for drop shipping
        $standardCost = $this->calculateShippingCost($method, $cart, $address);
        $dropShippingPremium = config('shipping.drop_shipping_premium', 2.00);

        return $standardCost + $dropShippingPremium;
    }
}