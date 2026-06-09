<?php

namespace Tests\Unit;

use App\Models\ShippingMethod;
use App\Services\ShippingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ShippingServiceTest extends TestCase
{
    use RefreshDatabase;

    private ShippingService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ShippingService();
    }

    private function makeMethod(array $overrides = []): ShippingMethod
    {
        return ShippingMethod::create(array_merge([
            'name' => 'Standard Shipping',
            'description' => 'Standard delivery',
            'base_rate' => 5.00,
            'weight_rate' => 0.50,
            'max_weight' => 50.0,
            'estimated_delivery_time' => '5-7 days',
        ], $overrides));
    }

    public function test_get_available_shipping_methods_returns_all_without_cart(): void
    {
        $method1 = $this->makeMethod(['name' => 'Standard']);
        $method2 = $this->makeMethod(['name' => 'Express', 'base_rate' => 15.00]);

        $methods = $this->service->getAvailableShippingMethods();

        $this->assertCount(2, $methods);
    }

    public function test_get_available_shipping_methods_returns_all_without_address(): void
    {
        $this->makeMethod();

        $methods = $this->service->getAvailableShippingMethods(['items' => []], null);

        $this->assertCount(1, $methods);
    }

    public function test_get_available_shipping_methods_filters_by_weight(): void
    {
        $lightMethod = $this->makeMethod(['name' => 'Light Only', 'max_weight' => 5.0]);
        $heavyMethod = $this->makeMethod(['name' => 'Heavy OK', 'max_weight' => 100.0]);

        $cart = [
            ['weight' => 3.0, 'quantity' => 2], // total 6kg
        ];

        $methods = $this->service->getAvailableShippingMethods($cart, '123 Main St');

        $this->assertFalse($methods->contains('id', $lightMethod->id));
        $this->assertTrue($methods->contains('id', $heavyMethod->id));
    }

    public function test_calculate_shipping_cost_uses_base_rate(): void
    {
        $method = $this->makeMethod(['base_rate' => 10.00, 'weight_rate' => 0.00]);

        $cart = [['weight' => 0, 'quantity' => 1]];
        $cost = $this->service->calculateShippingCost($method, $cart, '123 Main St');

        $this->assertEquals(10.00, $cost);
    }

    public function test_calculate_shipping_cost_includes_weight_rate(): void
    {
        $method = $this->makeMethod(['base_rate' => 5.00, 'weight_rate' => 2.00, 'max_weight' => 100]);

        $cart = [['weight' => 3.0, 'quantity' => 2]]; // 6kg total
        $cost = $this->service->calculateShippingCost($method, $cart, '123 Main St');

        // base(5) + weight(6 * 2) = 5 + 12 = 17
        $this->assertEquals(17.00, $cost);
    }

    public function test_calculate_dropshipping_cost_adds_premium(): void
    {
        $method = $this->makeMethod(['base_rate' => 5.00, 'weight_rate' => 0.00, 'max_weight' => 100]);
        config(['shipping.drop_shipping_premium' => 3.00]);

        $cart = [['weight' => 0, 'quantity' => 1]];
        $cost = $this->service->calculateDropShippingCost($method, $cart, '123 Main St');

        $this->assertEquals(8.00, $cost);
    }

    public function test_verify_address_returns_null_on_failure(): void
    {
        Http::fake([
            'api.address-verifier.com*' => Http::response(null, 500),
        ]);

        $result = $this->service->verifyAddress('123 Main St');

        $this->assertNull($result);
    }

    public function test_verify_address_returns_json_on_success(): void
    {
        Http::fake([
            'api.address-verifier.com*' => Http::response(['verified' => true], 200),
        ]);

        $result = $this->service->verifyAddress('123 Main St');

        $this->assertEquals(['verified' => true], $result);
    }
}
