<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderHistoryTest extends TestCase
{
    use RefreshDatabase;

    private function order(array $attrs): Order
    {
        return Order::create(array_merge([
            'customer_email' => 'x@example.com',
            'total_amount' => 42.00,
            'status' => Order::STATUS_PAID,
            'order_date' => now()->toDateString(),
        ], $attrs));
    }

    public function test_my_orders_lists_only_my_orders(): void
    {
        $me = User::factory()->create();
        $other = User::factory()->create();
        $mine = $this->order(['user_id' => $me->id]);
        $theirs = $this->order(['user_id' => $other->id]);
        $guest = $this->order(['user_id' => null]);

        $response = $this->actingAs($me)->get(route('orders.index'));

        $response->assertStatus(200);
        $response->assertSee(route('orders.show', $mine->id));
        $response->assertDontSee(route('orders.show', $theirs->id));
        $response->assertDontSee(route('orders.show', $guest->id));
    }

    public function test_can_view_my_own_order(): void
    {
        $me = User::factory()->create();
        $order = $this->order(['user_id' => $me->id]);

        $this->actingAs($me)->get(route('orders.show', $order->id))
            ->assertStatus(200)
            ->assertSee('42.00');
    }

    public function test_cannot_view_another_users_order_is_404(): void
    {
        $me = User::factory()->create();
        $other = User::factory()->create();
        $order = $this->order(['user_id' => $other->id]);

        // IDOR guard: someone else's order is not found for me (404, not 403, not 200).
        $this->actingAs($me)->get(route('orders.show', $order->id))->assertStatus(404);
    }

    public function test_guest_order_is_not_viewable_is_404(): void
    {
        $me = User::factory()->create();
        $order = $this->order(['user_id' => null]);

        $this->actingAs($me)->get(route('orders.show', $order->id))->assertStatus(404);
    }
}
