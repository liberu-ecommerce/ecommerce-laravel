<?php

namespace Tests\Feature;

use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderPaymentStatusTest extends TestCase
{
    use RefreshDatabase;

    private function order(string $status = 'pending'): Order
    {
        return Order::create([
            'customer_email' => 'b@example.com',
            'total_amount' => 50,
            'status' => $status,
        ]);
    }

    public function test_transition_to_paid_marks_payment_status_paid(): void
    {
        // The bug: analytics, LTV, invoices and widgets all filter payment_status='paid',
        // but a captured payment only set `status`, leaving payment_status unset → zero revenue.
        $order = $this->order();

        $order->transitionTo(Order::STATUS_PAID);

        $this->assertSame('paid', $order->fresh()->payment_status);
    }

    public function test_transition_to_failed_marks_payment_status_failed(): void
    {
        $order = $this->order();

        $order->transitionTo(Order::STATUS_FAILED);

        $this->assertSame('failed', $order->fresh()->payment_status);
    }

    public function test_refund_transition_does_not_clobber_paid_status(): void
    {
        $order = $this->order();
        $order->transitionTo(Order::STATUS_PAID);

        $order->transitionTo(Order::STATUS_REFUNDED);

        // A refunded order was still genuinely paid — analytics counts gross revenue;
        // refund_total tracks the money returned.
        $this->assertSame('paid', $order->fresh()->payment_status);
    }
}
