<?php

namespace Tests\Feature\Api;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class OrderStatusApiTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        Role::findOrCreate('super_admin', 'web');

        return User::factory()->create()->assignRole('super_admin');
    }

    private function order(string $status = 'paid'): Order
    {
        return Order::create([
            'customer_email' => 'buyer@example.com',
            'total_amount' => 50,
            'status' => $status,
        ]);
    }

    public function test_unauthenticated_cannot_change_status(): void
    {
        $order = $this->order();
        $this->postJson("/api/orders/{$order->id}/status", ['status' => 'processing'])->assertStatus(401);
    }

    public function test_non_admin_cannot_change_status(): void
    {
        $order = $this->order();
        Sanctum::actingAs(User::factory()->create());

        $this->postJson("/api/orders/{$order->id}/status", ['status' => 'processing'])->assertStatus(403);
        $this->assertSame('paid', $order->refresh()->status);
    }

    public function test_admin_advances_a_paid_order_to_processing_with_audit_history(): void
    {
        $order = $this->order('paid');
        Sanctum::actingAs($this->admin());

        $this->postJson("/api/orders/{$order->id}/status", ['status' => 'processing', 'notes' => 'picking'])
            ->assertStatus(200);

        $order->refresh();
        $this->assertSame(Order::STATUS_PROCESSING, $order->status);
        $this->assertNotNull($order->statusHistory()->where('to_status', 'processing')->first());
    }

    public function test_admin_completes_a_processing_order(): void
    {
        $order = $this->order('processing');
        Sanctum::actingAs($this->admin());

        $this->postJson("/api/orders/{$order->id}/status", ['status' => 'completed'])->assertStatus(200);

        $this->assertSame(Order::STATUS_COMPLETED, $order->refresh()->status);
    }

    public function test_admin_cancels_a_paid_order(): void
    {
        $order = $this->order('paid');
        Sanctum::actingAs($this->admin());

        $this->postJson("/api/orders/{$order->id}/status", ['status' => 'cancelled'])->assertStatus(200);

        $this->assertSame(Order::STATUS_CANCELLED, $order->refresh()->status);
    }

    public function test_illegal_transition_is_rejected_and_leaves_the_order_unchanged(): void
    {
        $order = $this->order('completed');
        Sanctum::actingAs($this->admin());

        // completed -> processing is not a legal transition.
        $this->postJson("/api/orders/{$order->id}/status", ['status' => 'processing'])->assertStatus(422);

        $this->assertSame(Order::STATUS_COMPLETED, $order->refresh()->status);
    }

    public function test_money_statuses_are_not_settable_via_this_endpoint(): void
    {
        $order = $this->order('paid');
        Sanctum::actingAs($this->admin());

        // refunded is a money outcome — must go through the refund flow, not here.
        $this->postJson("/api/orders/{$order->id}/status", ['status' => 'refunded'])->assertStatus(422);

        $this->assertSame('paid', $order->refresh()->status);
    }
}
