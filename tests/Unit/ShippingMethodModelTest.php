<?php

namespace Tests\Unit;

use App\Models\ShippingMethod;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShippingMethodModelTest extends TestCase
{
    use RefreshDatabase;

    private function makeShipping(array $overrides = []): ShippingMethod
    {
        return ShippingMethod::create(array_merge([
            'name' => 'Standard Shipping',
            'description' => 'Arrives in 5-7 business days',
            'base_rate' => 5.99,
            'weight_rate' => 0.50,
            'max_weight' => 50.0,
            'estimated_delivery_time' => '5-7 days',
        ], $overrides));
    }

    public function test_shipping_method_can_be_created(): void
    {
        $method = $this->makeShipping();

        $this->assertInstanceOf(ShippingMethod::class, $method);
        $this->assertEquals('Standard Shipping', $method->name);
    }

    public function test_base_rate_cast_to_float(): void
    {
        $method = $this->makeShipping(['base_rate' => 9.99]);

        $this->assertIsFloat($method->fresh()->base_rate);
        $this->assertEquals(9.99, $method->fresh()->base_rate);
    }

    public function test_weight_rate_cast_to_float(): void
    {
        $method = $this->makeShipping(['weight_rate' => 1.25]);

        $this->assertIsFloat($method->fresh()->weight_rate);
    }

    public function test_max_weight_cast_to_float(): void
    {
        $method = $this->makeShipping(['max_weight' => 100.0]);

        $this->assertIsFloat($method->fresh()->max_weight);
        $this->assertEquals(100.0, $method->fresh()->max_weight);
    }

    public function test_multiple_shipping_methods_can_coexist(): void
    {
        $this->makeShipping(['name' => 'Standard', 'base_rate' => 5.99]);
        $this->makeShipping(['name' => 'Express', 'base_rate' => 14.99]);

        $this->assertCount(2, ShippingMethod::all());
    }
}
