<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderHistoryControllerTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(): User
    {
        return User::factory()->withPersonalTeam()->create();
    }

    private function makeOrder(User $user, array $overrides = []): Order
    {
        $customer = Customer::create([
            'user_id' => $user->id,
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => $user->email,
            'phone_number' => '555-1234',
            'address' => '100 Main St',
            'city' => 'Springfield',
            'state' => 'IL',
            'postal_code' => '62701',
        ]);

        return Order::create(array_merge([
            'user_id' => $user->id,
            'customer_id' => $customer->id,
            'customer_email' => $user->email,
            'total_amount' => 100.00,
            'order_date' => now()->toDateString(),
            'payment_status' => 'pending',
            'status' => 'completed',
            'shipping_status' => 'delivered',
        ], $overrides));
    }

    public function test_index_redirects_guests(): void
    {
        $response = $this->get('/orders');

        $response->assertRedirect();
    }

    public function test_index_shows_users_orders(): void
    {
        $user = $this->makeUser();
        $this->makeOrder($user);

        $response = $this->actingAs($user)->get('/orders');

        $response->assertStatus(200);
        $response->assertViewIs('orders.history');
        $response->assertViewHas('orders');
    }

    public function test_index_does_not_show_other_users_orders(): void
    {
        $userA = $this->makeUser();
        $userB = $this->makeUser();
        $orderA = $this->makeOrder($userA);

        $response = $this->actingAs($userB)->get('/orders');

        $orders = $response->viewData('orders');
        $this->assertFalse($orders->contains('id', $orderA->id));
    }

    public function test_show_returns_order_for_owner(): void
    {
        $user = $this->makeUser();
        $order = $this->makeOrder($user);

        $response = $this->actingAs($user)->get("/orders/{$order->id}");

        $response->assertStatus(200);
        $response->assertViewIs('orders.show');
        $response->assertViewHas('order');
    }

    public function test_show_returns_404_for_unknown_order(): void
    {
        $user = $this->makeUser();

        $response = $this->actingAs($user)->get('/orders/9999');

        $response->assertStatus(404);
    }

    public function test_show_returns_404_when_order_belongs_to_other_user(): void
    {
        $userA = $this->makeUser();
        $userB = $this->makeUser();
        $order = $this->makeOrder($userA);

        // Scoped to the user's own orders — a foreign order is not found (no
        // existence-confirming 403).
        $response = $this->actingAs($userB)->get("/orders/{$order->id}");

        $response->assertStatus(404);
    }
}
