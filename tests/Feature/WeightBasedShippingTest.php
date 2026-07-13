<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ShippingMethod;
use App\Services\ShippingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * A shipping method with a weight_rate must actually charge for weight. The cart is
 * keyed by product_id and carries no weight, so ShippingService looks the product
 * weight up. Before this it always added $0 (charging base_rate only).
 */
class WeightBasedShippingTest extends TestCase
{
    use RefreshDatabase;

    public function test_weight_rate_charges_for_the_product_weight(): void
    {
        $product = Product::factory()->create(['weight' => 2]); // 2kg each
        $method = ShippingMethod::create(['name' => 'Std', 'base_rate' => 5, 'weight_rate' => 3]);

        // 4 units x 2kg = 8kg; base 5 + 8 x 3 = 29.
        $cost = app(ShippingService::class)->calculateShippingCost(
            $method,
            [$product->id => ['quantity' => 4, 'price' => 10]],
            '123 Test St'
        );

        $this->assertEquals(29.0, $cost);
    }

    public function test_zero_weight_rate_charges_only_the_base(): void
    {
        $product = Product::factory()->create(['weight' => 2]);
        $method = ShippingMethod::create(['name' => 'Flat', 'base_rate' => 5, 'weight_rate' => 0]);

        $cost = app(ShippingService::class)->calculateShippingCost(
            $method,
            [$product->id => ['quantity' => 4, 'price' => 10]],
            '123 Test St'
        );

        $this->assertEquals(5.0, $cost);
    }
}
