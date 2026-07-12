<?php

namespace Tests\Feature;

use App\Exceptions\InvalidOrderTransitionException;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderStateMachineTest extends TestCase
{
    use RefreshDatabase;

    private function order(string $status): Order
    {
        return Order::create([
            'customer_email' => 'buyer@example.com',
            'total_amount' => 100,
            'status' => $status,
        ]);
    }

    public function test_valid_transition_updates_status_and_records_history(): void
    {
        $order = $this->order(Order::STATUS_PENDING);

        $order->transitionTo(Order::STATUS_PAID, notes: 'payment captured');

        $this->assertSame(Order::STATUS_PAID, $order->fresh()->status);

        $history = $order->statusHistory()->first();
        $this->assertNotNull($history, 'No status history was recorded');
        $this->assertSame(Order::STATUS_PENDING, $history->from_status);
        $this->assertSame(Order::STATUS_PAID, $history->to_status);
        $this->assertSame('payment captured', $history->notes);
    }

    public function test_invalid_transition_throws_and_leaves_state_unchanged(): void
    {
        $order = $this->order(Order::STATUS_PENDING);

        try {
            $order->transitionTo(Order::STATUS_COMPLETED);
            $this->fail('Expected InvalidOrderTransitionException for pending -> completed');
        } catch (InvalidOrderTransitionException $e) {
            // expected: pending cannot jump straight to completed
        }

        $this->assertSame(Order::STATUS_PENDING, $order->fresh()->status);
        $this->assertSame(0, OrderStatusHistory::count(), 'A rejected transition must not record history');
    }
}
