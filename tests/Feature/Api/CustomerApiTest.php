<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_endpoints_require_authentication(): void
    {
        $this->getJson('/api/customer')->assertUnauthorized();
        $this->putJson('/api/customer', [])->assertUnauthorized();
    }

    public function test_show_returns_the_users_own_customer_creating_it(): void
    {
        $user = User::factory()->create(['name' => 'Jane Doe', 'email' => 'jane@example.com']);

        $this->actingAs($user)->getJson('/api/customer')
            ->assertOk()
            ->assertJsonPath('data.user_id', $user->id)
            ->assertJsonPath('data.first_name', 'Jane')
            ->assertJsonPath('data.email', 'jane@example.com');

        $this->assertDatabaseHas('customers', ['user_id' => $user->id]);
    }

    public function test_show_is_idempotent(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->getJson('/api/customer')->assertOk();
        $this->actingAs($user)->getJson('/api/customer')->assertOk();

        $this->assertDatabaseCount('customers', 1);
    }

    public function test_update_persists_the_users_profile(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->putJson('/api/customer', [
            'first_name' => 'Updated', 'city' => 'Berlin', 'postal_code' => '10115',
        ])->assertOk()->assertJsonPath('data.city', 'Berlin');

        $this->assertDatabaseHas('customers', [
            'user_id' => $user->id, 'first_name' => 'Updated', 'city' => 'Berlin', 'postal_code' => '10115',
        ]);
    }

    public function test_update_validates_input(): void
    {
        $this->actingAs(User::factory()->create())
            ->putJson('/api/customer', ['email' => 'not-an-email'])
            ->assertStatus(422);
    }

    public function test_each_user_gets_their_own_customer(): void
    {
        $a = User::factory()->create();
        $b = User::factory()->create();

        $this->actingAs($a)->putJson('/api/customer', ['first_name' => 'Alice']);
        $bCustomer = $this->actingAs($b)->getJson('/api/customer')->json('data');

        $this->assertEquals($b->id, $bCustomer['user_id']);
        $this->assertNotEquals('Alice', $bCustomer['first_name']);
    }
}
