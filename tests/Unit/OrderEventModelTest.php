<?php

namespace Tests\Unit;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderEventModelTest extends TestCase
{
    use RefreshDatabase;

    private function makeOrder(): Order
    {
        $user = User::factory()->create();
        $customer = Customer::create([
            'user_id' => $user->id,
            'first_name' => 'Eve',
            'last_name' => 'Event',
            'email' => $user->email,
            'phone_number' => '555-0001',
            'address' => '1 Event Lane',
            'city' => 'Chicago',
            'state' => 'IL',
            'postal_code' => '60601',
        ]);
        return Order::create([
            'customer_id' => $customer->id,
            'customer_email' => $user->email,
            'total_amount' => 50.00,
            'order_date' => now()->toDateString(),
            'payment_status' => 'pending',
            'shipping_status' => 'pending',
        ]);
    }

    public function test_order_event_can_be_created(): void
    {
        $order = $this->makeOrder();

        $event = OrderEvent::create([
            'order_id' => $order->id,
            'event_type' => 'status_change',
            'description' => 'Order placed',
        ]);

        $this->assertInstanceOf(OrderEvent::class, $event);
        $this->assertEquals('status_change', $event->event_type);
    }

    public function test_metadata_is_array_cast(): void
    {
        $order = $this->makeOrder();

        $event = OrderEvent::create([
            'order_id' => $order->id,
            'event_type' => 'payment',
            'description' => 'Payment received',
            'metadata' => ['amount' => 50.00, 'method' => 'card'],
        ]);

        $this->assertIsArray($event->fresh()->metadata);
        $this->assertEquals('card', $event->fresh()->metadata['method']);
    }

    public function test_log_static_method_creates_event(): void
    {
        $order = $this->makeOrder();
        $user = User::factory()->create();

        $event = OrderEvent::log(
            $order->id,
            'shipped',
            'Order shipped via FedEx',
            ['tracking' => '1234'],
            $user->id
        );

        $this->assertInstanceOf(OrderEvent::class, $event);
        $this->assertEquals('shipped', $event->event_type);
        $this->assertEquals($user->id, $event->triggered_by);
    }

    public function test_belongs_to_order(): void
    {
        $order = $this->makeOrder();
        $event = OrderEvent::log($order->id, 'test', 'test event');

        $this->assertInstanceOf(Order::class, $event->order);
        $this->assertEquals($order->id, $event->order->id);
    }
}
