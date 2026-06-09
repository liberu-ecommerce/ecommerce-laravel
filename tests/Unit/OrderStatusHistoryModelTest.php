<?php

namespace Tests\Unit;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderStatusHistoryModelTest extends TestCase
{
    use RefreshDatabase;

    private function makeOrder(): Order
    {
        $user = User::factory()->create();
        $customer = Customer::create([
            'user_id' => $user->id,
            'first_name' => 'Status',
            'last_name' => 'History',
            'email' => $user->email,
            'phone_number' => '555-0003',
            'address' => '3 History Rd',
            'city' => 'Chicago',
            'state' => 'IL',
            'postal_code' => '60603',
        ]);
        return Order::create([
            'customer_id' => $customer->id,
            'customer_email' => $user->email,
            'total_amount' => 100.00,
            'order_date' => now()->toDateString(),
            'payment_status' => 'paid',
            'shipping_status' => 'pending',
        ]);
    }

    public function test_order_status_history_can_be_created(): void
    {
        $order = $this->makeOrder();
        $user = User::factory()->create();

        $history = OrderStatusHistory::create([
            'order_id' => $order->id,
            'from_status' => 'pending',
            'to_status' => 'processing',
            'changed_by' => $user->id,
            'customer_notified' => true,
        ]);

        $this->assertInstanceOf(OrderStatusHistory::class, $history);
        $this->assertEquals('processing', $history->to_status);
    }

    public function test_customer_notified_is_boolean_cast(): void
    {
        $order = $this->makeOrder();

        $history = OrderStatusHistory::create([
            'order_id' => $order->id,
            'from_status' => 'pending',
            'to_status' => 'shipped',
            'customer_notified' => true,
        ]);

        $this->assertIsBool($history->fresh()->customer_notified);
        $this->assertTrue($history->fresh()->customer_notified);
    }

    public function test_belongs_to_order(): void
    {
        $order = $this->makeOrder();

        $history = OrderStatusHistory::create([
            'order_id' => $order->id,
            'from_status' => 'processing',
            'to_status' => 'completed',
        ]);

        $this->assertInstanceOf(Order::class, $history->order);
        $this->assertEquals($order->id, $history->order->id);
    }
}
