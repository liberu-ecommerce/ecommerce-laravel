<?php

namespace Tests\Unit;

use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTotalAmountTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Intent lock: an order total must keep its cents. This passes on sqlite
     * regardless (loose typing), but on MySQL it fails if total_amount is an
     * integer column (99.99 -> 100) and passes once it is decimal(10,2).
     */
    public function test_order_total_amount_preserves_cents(): void
    {
        $order = Order::create([
            'customer_email' => 'buyer@example.com',
            'total_amount' => 99.99,
            'status' => Order::STATUS_PENDING,
        ]);

        $this->assertSame('99.99', (string) $order->fresh()->total_amount);
    }
}
