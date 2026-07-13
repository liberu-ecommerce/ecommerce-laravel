<?php

namespace Tests\Unit;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Refund;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class RefundModelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
    }

    private function makeOrder(): Order
    {
        $user = User::factory()->create();
        $customer = Customer::create([
            'user_id' => $user->id,
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => $user->email,
            'phone_number' => '555-0000',
            'address' => '123 Main St',
            'city' => 'Springfield',
            'state' => 'IL',
            'postal_code' => '62701',
        ]);
        return Order::create([
            'customer_id' => $customer->id,
            'customer_email' => $user->email,
            'total_amount' => 100.00,
            'order_date' => now()->toDateString(),
            'payment_status' => 'paid',
            'status' => 'delivered',
            'shipping_status' => 'delivered',
            'refund_total' => 0,
        ]);
    }

    private function makeRefund(Order $order, array $overrides = []): Refund
    {
        return Refund::create(array_merge([
            'order_id' => $order->id,
            'amount' => 25.00,
            'reason' => 'defective',
            'status' => 'pending',
            'restock_items' => false,
        ], $overrides));
    }

    public function test_process_changes_status_to_processed(): void
    {
        $order = $this->makeOrder();
        $refund = $this->makeRefund($order);

        $result = $refund->process();

        $this->assertTrue($result);
        $this->assertEquals('processed', $refund->fresh()->status);
    }

    public function test_process_sets_processed_at(): void
    {
        $order = $this->makeOrder();
        $refund = $this->makeRefund($order);

        $refund->process();

        $this->assertNotNull($refund->fresh()->processed_at);
    }

    public function test_process_returns_false_when_not_pending(): void
    {
        $order = $this->makeOrder();
        $refund = $this->makeRefund($order, ['status' => 'processed']);

        $result = $refund->process();

        $this->assertFalse($result);
    }

    public function test_process_increments_order_refund_total(): void
    {
        $order = $this->makeOrder();
        $refund = $this->makeRefund($order, ['amount' => 30.00]);

        $refund->process();

        $this->assertEquals(30.00, $order->fresh()->refund_total);
    }

    public function test_belongs_to_order(): void
    {
        $order = $this->makeOrder();
        $refund = $this->makeRefund($order);

        $this->assertInstanceOf(Order::class, $refund->order);
        $this->assertEquals($order->id, $refund->order->id);
    }

    public function test_amount_is_decimal_cast(): void
    {
        $order = $this->makeOrder();
        $refund = $this->makeRefund($order, ['amount' => 19.99]);

        $this->assertEquals('19.99', $refund->fresh()->amount);
    }

    public function test_restock_items_is_boolean_cast(): void
    {
        $order = $this->makeOrder();
        $refund = $this->makeRefund($order, ['restock_items' => false]);

        $this->assertFalse($refund->fresh()->restock_items);
    }
}
