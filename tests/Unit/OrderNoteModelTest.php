<?php

namespace Tests\Unit;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderNote;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderNoteModelTest extends TestCase
{
    use RefreshDatabase;

    private function makeOrder(): Order
    {
        $user = User::factory()->create();
        $customer = Customer::create([
            'user_id' => $user->id,
            'first_name' => 'Note',
            'last_name' => 'Tester',
            'email' => $user->email,
            'phone_number' => '555-0002',
            'address' => '2 Note Ave',
            'city' => 'Chicago',
            'state' => 'IL',
            'postal_code' => '60602',
        ]);
        return Order::create([
            'customer_id' => $customer->id,
            'customer_email' => $user->email,
            'total_amount' => 75.00,
            'order_date' => now()->toDateString(),
            'payment_status' => 'paid',
            'shipping_status' => 'pending',
        ]);
    }

    public function test_create_customer_note(): void
    {
        $order = $this->makeOrder();

        $note = OrderNote::createCustomerNote($order->id, 'Please leave at front door');

        $this->assertInstanceOf(OrderNote::class, $note);
        $this->assertEquals('customer', $note->type);
        $this->assertTrue($note->customer_visible);
    }

    public function test_create_internal_note(): void
    {
        $order = $this->makeOrder();

        $note = OrderNote::createInternalNote($order->id, 'VIP customer — expedite');

        $this->assertEquals('internal', $note->type);
        $this->assertFalse($note->customer_visible);
    }

    public function test_create_system_note(): void
    {
        $order = $this->makeOrder();

        $note = OrderNote::createSystemNote($order->id, 'Payment confirmed automatically');

        $this->assertEquals('system', $note->type);
        $this->assertFalse($note->customer_visible);
        $this->assertTrue($note->is_system_note);
    }

    public function test_customer_visible_is_boolean_cast(): void
    {
        $order = $this->makeOrder();
        $note = OrderNote::createCustomerNote($order->id, 'Test');

        $this->assertIsBool($note->fresh()->customer_visible);
    }

    public function test_belongs_to_order(): void
    {
        $order = $this->makeOrder();
        $note = OrderNote::createSystemNote($order->id, 'System note');

        $this->assertInstanceOf(Order::class, $note->order);
        $this->assertEquals($order->id, $note->order->id);
    }
}
