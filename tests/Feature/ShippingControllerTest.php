<?php

namespace Tests\Feature;

use App\Models\ShippingMethod;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShippingControllerTest extends TestCase
{
    use RefreshDatabase;

    private function makeShipping(array $overrides = []): ShippingMethod
    {
        return ShippingMethod::create(array_merge([
            'name' => 'Standard Shipping',
            'description' => '5-7 days',
            'base_rate' => 5.99,
            'weight_rate' => 0.50,
            'max_weight' => 50.0,
            'estimated_delivery_time' => '5-7 business days',
        ], $overrides));
    }

    public function test_index_returns_view(): void
    {
        $this->makeShipping();

        $response = $this->get('/shipping');

        $response->assertStatus(200);
        $response->assertViewIs('shipping.index');
        $response->assertViewHas('shippingMethods');
    }

    public function test_store_creates_new_shipping_method(): void
    {
        $response = $this->post('/shipping', [
            'name' => 'Express Shipping',
            'description' => '2-3 days',
            'base_rate' => 15.99,
            'weight_rate' => 1.00,
            'estimated_delivery_time' => '2-3 business days',
        ]);

        $response->assertRedirect(route('shipping.index'));
        $this->assertDatabaseHas('shipping_methods', [
            'name' => 'Express Shipping',
        ]);
    }

    public function test_store_requires_name(): void
    {
        $response = $this->post('/shipping', [
            'base_rate' => 5.99,
            'estimated_delivery_time' => '3-5 days',
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_store_requires_base_rate(): void
    {
        $response = $this->post('/shipping', [
            'name' => 'Test',
            'estimated_delivery_time' => '3-5 days',
        ]);

        $response->assertSessionHasErrors('base_rate');
    }

    public function test_store_requires_estimated_delivery_time(): void
    {
        $response = $this->post('/shipping', [
            'name' => 'Test',
            'base_rate' => 5.99,
        ]);

        $response->assertSessionHasErrors('estimated_delivery_time');
    }

    public function test_store_persists_is_active_false_when_unchecked(): void
    {
        // is_active is collected by the controller; it must actually persist.
        $this->post('/shipping', [
            'name' => 'Retired Method',
            'base_rate' => 5.99,
            'estimated_delivery_time' => '3-5 business days',
            // is_active checkbox not submitted -> should store false
        ]);

        $this->assertDatabaseHas('shipping_methods', [
            'name' => 'Retired Method',
            'is_active' => false,
        ]);
    }

    public function test_update_can_deactivate_shipping_method(): void
    {
        $method = $this->makeShipping(); // active by default

        $this->put("/shipping/{$method->id}", [
            'name' => 'Standard Shipping',
            'base_rate' => 5.99,
            'estimated_delivery_time' => '5-7 business days',
            // is_active not submitted -> deactivate
        ]);

        $this->assertFalse($method->fresh()->is_active);
    }

    public function test_update_changes_shipping_method(): void
    {
        $method = $this->makeShipping();

        $response = $this->put("/shipping/{$method->id}", [
            'name' => 'Updated Name',
            'base_rate' => 9.99,
            'estimated_delivery_time' => '3-4 days',
        ]);

        $response->assertRedirect(route('shipping.index'));
        $this->assertEquals('Updated Name', $method->fresh()->name);
    }

    public function test_destroy_deletes_shipping_method(): void
    {
        $method = $this->makeShipping();

        $response = $this->delete("/shipping/{$method->id}");

        $response->assertRedirect(route('shipping.index'));
        $this->assertNull(ShippingMethod::find($method->id));
    }
}
