<?php

namespace Tests\Feature\Api;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OrderApiTest extends TestCase
{
    use RefreshDatabase;

    private function order(array $attrs): Order
    {
        return Order::create(array_merge([
            'customer_email' => 'x@example.com',
            'total_amount' => 25,
            'status' => Order::STATUS_PAID,
            'order_date' => now()->toDateString(),
        ], $attrs));
    }

    public function test_unauthenticated_cannot_list_orders(): void
    {
        $this->getJson('/api/orders')->assertStatus(401);
    }

    public function test_lists_only_the_authenticated_users_orders(): void
    {
        $me = User::factory()->create();
        $other = User::factory()->create();
        $this->order(['user_id' => $me->id, 'total_amount' => 10]);
        $this->order(['user_id' => $me->id, 'total_amount' => 20]);
        $this->order(['user_id' => $other->id, 'total_amount' => 99]);
        $this->order(['user_id' => null]); // guest order — never exposed via the authed API

        Sanctum::actingAs($me);
        $res = $this->getJson('/api/orders');

        $res->assertStatus(200)->assertJsonStructure(['data', 'total', 'per_page', 'current_page']);
        $this->assertSame(2, $res->json('total'));
        $userIds = collect($res->json('data'))->pluck('user_id')->unique()->values()->all();
        $this->assertSame([$me->id], $userIds);
    }

    public function test_show_returns_own_order(): void
    {
        $me = User::factory()->create();
        $order = $this->order(['user_id' => $me->id]);

        Sanctum::actingAs($me);

        $this->getJson("/api/orders/{$order->id}")
            ->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', $order->id);
    }

    public function test_cannot_view_another_users_order(): void
    {
        $me = User::factory()->create();
        $other = User::factory()->create();
        $order = $this->order(['user_id' => $other->id]);

        Sanctum::actingAs($me);

        // IDOR guard: someone else's order is simply not found for me.
        $this->getJson("/api/orders/{$order->id}")->assertStatus(404);
    }

    public function test_show_missing_order_is_404(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->getJson('/api/orders/999999')->assertStatus(404);
    }
}
