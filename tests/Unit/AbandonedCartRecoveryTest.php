<?php

namespace Tests\Unit;

use App\Models\AbandonedCart;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AbandonedCartRecoveryTest extends TestCase
{
    use RefreshDatabase;

    private function cart(): AbandonedCart
    {
        return AbandonedCart::create([
            'customer_email' => 'buyer@example.com',
            'session_id' => 'sess-'.uniqid(),
            'cart_token' => 'tok-'.uniqid(),
            'total_amount' => 50,
            'abandoned_at' => now(),
            'line_items' => [['product_id' => 1, 'quantity' => 2]],
        ]);
    }

    public function test_recovery_attempts_relation_resolves(): void
    {
        $cart = $this->cart();

        // Relation pointed at a nonexistent AbandonedCartEmail model -> fatal.
        $this->assertCount(0, $cart->recoveryEmails);
    }

    public function test_mark_as_recovered_records_recovered_at_and_order(): void
    {
        $cart = $this->cart();
        $order = Order::create([
            'customer_email' => 'buyer@example.com',
            'total_amount' => 50,
            'status' => Order::STATUS_PAID,
        ]);

        $cart->markAsRecovered($order);

        $fresh = $cart->fresh();
        $this->assertNotNull($fresh->recovered_at);
        $this->assertSame($order->id, $fresh->recovery_order_id);
        $this->assertTrue($fresh->isRecovered());
    }
}
