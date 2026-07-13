<?php

namespace Tests\Feature;

use App\Models\ShippingMethod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ShippingAdminAuthTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        Role::findOrCreate('super_admin', 'web');

        return User::factory()->create()->assignRole('super_admin');
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Express',
            'base_rate' => 9.99,
            'estimated_delivery_time' => '2 days',
        ], $overrides);
    }

    public function test_guests_cannot_access_shipping_management(): void
    {
        $this->get('/shipping')->assertRedirect('/login');
        $this->post('/shipping', $this->payload())->assertRedirect('/login');

        $method = ShippingMethod::create($this->payload());
        $this->delete("/shipping/{$method->id}")->assertRedirect('/login');
        $this->assertDatabaseHas('shipping_methods', ['id' => $method->id]);
    }

    public function test_non_admin_cannot_manage_shipping(): void
    {
        $user = User::factory()->create(); // no role

        $this->actingAs($user)->post('/shipping', $this->payload())->assertStatus(403);

        $method = ShippingMethod::create($this->payload());
        $this->actingAs($user)->delete("/shipping/{$method->id}")->assertStatus(403);
        $this->assertDatabaseHas('shipping_methods', ['id' => $method->id]);
    }

    public function test_admin_can_create_shipping_method(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post('/shipping', $this->payload(['name' => 'Overnight']))
            ->assertRedirect(route('shipping.index'));

        $this->assertDatabaseHas('shipping_methods', ['name' => 'Overnight']);
    }

    public function test_admin_can_delete_shipping_method(): void
    {
        $admin = $this->admin();
        $method = ShippingMethod::create($this->payload());

        $this->actingAs($admin)->delete("/shipping/{$method->id}")
            ->assertRedirect(route('shipping.index'));

        $this->assertDatabaseMissing('shipping_methods', ['id' => $method->id]);
    }

    public function test_explicit_is_active_false_deactivates_method(): void
    {
        $admin = $this->admin();
        $method = ShippingMethod::create($this->payload(['is_active' => true]));

        // has() treated key-presence as true, so is_active=0 couldn't deactivate.
        $this->actingAs($admin)->put("/shipping/{$method->id}", $this->payload(['is_active' => 0]));

        $this->assertFalse((bool) $method->fresh()->is_active);
    }
}
