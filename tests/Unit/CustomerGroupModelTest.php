<?php

namespace Tests\Unit;

use App\Models\Customer;
use App\Models\CustomerGroup;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerGroupModelTest extends TestCase
{
    use RefreshDatabase;

    private function makeGroup(array $overrides = []): CustomerGroup
    {
        return CustomerGroup::create(array_merge([
            'name' => 'VIP Customers',
            'discount_percentage' => 10,
            'discount_amount' => 0,
            'minimum_order_amount' => 50,
            'free_shipping_threshold' => 100,
            'is_active' => true,
        ], $overrides));
    }

    private function makeCustomer(): Customer
    {
        $user = User::factory()->create();
        return Customer::create([
            'user_id' => $user->id,
            'first_name' => 'Group',
            'last_name' => 'Member',
            'email' => $user->email,
            'phone_number' => '555-1111',
            'address' => '100 Oak St',
            'city' => 'Springfield',
            'state' => 'IL',
            'postal_code' => '62701',
        ]);
    }

    public function test_calculate_discount_with_percentage(): void
    {
        $group = $this->makeGroup(['discount_percentage' => 10, 'minimum_order_amount' => 0]);

        $discount = $group->calculateDiscount(100.00);

        $this->assertEquals(10.0, $discount);
    }

    public function test_calculate_discount_with_flat_amount(): void
    {
        $group = $this->makeGroup([
            'discount_percentage' => 0,
            'discount_amount' => 15,
            'minimum_order_amount' => 0,
        ]);

        $discount = $group->calculateDiscount(100.00);

        $this->assertEquals(15.0, $discount);
    }

    public function test_calculate_discount_returns_zero_below_minimum(): void
    {
        $group = $this->makeGroup(['minimum_order_amount' => 100]);

        $discount = $group->calculateDiscount(50.00);

        $this->assertEquals(0, $discount);
    }

    public function test_calculate_discount_returns_zero_when_inactive(): void
    {
        $group = $this->makeGroup(['is_active' => false, 'minimum_order_amount' => 0]);

        $discount = $group->calculateDiscount(100.00);

        $this->assertEquals(0, $discount);
    }

    public function test_qualifies_for_free_shipping(): void
    {
        $group = $this->makeGroup(['free_shipping_threshold' => 100]);

        $this->assertTrue($group->qualifiesForFreeShipping(150.00));
        $this->assertFalse($group->qualifiesForFreeShipping(50.00));
    }

    public function test_qualifies_for_free_shipping_false_when_no_threshold(): void
    {
        $group = $this->makeGroup(['free_shipping_threshold' => 0]);

        $this->assertFalse($group->qualifiesForFreeShipping(500.00));
    }

    public function test_add_and_remove_customer(): void
    {
        $group = $this->makeGroup();
        $customer = $this->makeCustomer();

        $group->addCustomer($customer);
        $this->assertTrue($group->hasCustomer($customer));

        $group->removeCustomer($customer);
        $this->assertFalse($group->hasCustomer($customer));
    }

    public function test_active_scope(): void
    {
        $active = $this->makeGroup(['name' => 'Active Group', 'is_active' => true]);
        $inactive = $this->makeGroup(['name' => 'Inactive Group', 'is_active' => false]);

        $results = CustomerGroup::active()->pluck('id');

        $this->assertContains($active->id, $results);
        $this->assertNotContains($inactive->id, $results);
    }
}
