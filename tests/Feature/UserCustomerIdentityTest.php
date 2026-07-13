<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * A Customer is the same identity as a User, linked 1:1 by customers.user_id.
 * getOrCreateCustomer() resolves (creating if needed) that record from the user.
 */
class UserCustomerIdentityTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_or_create_customer_links_a_customer_to_the_user(): void
    {
        $user = User::factory()->create(['name' => 'Jane Doe', 'email' => 'jane@example.com']);

        $customer = $user->getOrCreateCustomer();

        $this->assertEquals($user->id, $customer->user_id);
        $this->assertEquals('Jane', $customer->first_name);
        $this->assertEquals('Doe', $customer->last_name);
        $this->assertEquals('jane@example.com', $customer->email);
        $this->assertTrue($user->refresh()->customer->is($customer));
        $this->assertTrue($customer->user->is($user));
    }

    public function test_get_or_create_customer_is_idempotent(): void
    {
        $user = User::factory()->create();

        $first = $user->getOrCreateCustomer();
        $second = $user->refresh()->getOrCreateCustomer();

        $this->assertEquals($first->id, $second->id);
        $this->assertDatabaseCount('customers', 1);
    }

    public function test_a_users_customer_starts_minimal_without_profile_fields(): void
    {
        // Regression: phone/address/city/state/postal are now nullable so a user's
        // customer record can exist before those are filled.
        $customer = User::factory()->create()->getOrCreateCustomer();

        $this->assertNull($customer->phone_number);
        $this->assertNull($customer->address);
        $this->assertNull($customer->postal_code);
    }

    public function test_a_single_word_name_still_produces_a_valid_customer(): void
    {
        $customer = User::factory()->create(['name' => 'Cher'])->getOrCreateCustomer();

        $this->assertEquals('Cher', $customer->first_name);
        $this->assertSame('', $customer->last_name);
    }

    public function test_guest_customer_without_a_user_is_still_valid(): void
    {
        // The link is nullable — guest checkout customers keep working with no user_id.
        $customer = Customer::create(['first_name' => 'Guest', 'last_name' => 'Buyer', 'email' => 'g@example.com']);

        $this->assertNull($customer->user_id);
        $this->assertNull($customer->user);
    }
}
