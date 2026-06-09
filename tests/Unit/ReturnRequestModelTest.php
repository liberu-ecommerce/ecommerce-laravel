<?php

namespace Tests\Unit;

use App\Models\Customer;
use App\Models\Order;
use App\Models\ReturnRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReturnRequestModelTest extends TestCase
{
    use RefreshDatabase;

    private function makeOrderAndUser(): array
    {
        $user = User::factory()->create();
        $customer = Customer::create([
            'user_id' => $user->id,
            'first_name' => 'John',
            'last_name' => 'Return',
            'email' => $user->email,
            'phone_number' => '555-9999',
            'address' => '456 Oak Ave',
            'city' => 'Chicago',
            'state' => 'IL',
            'postal_code' => '60601',
        ]);
        $order = Order::create([
            'customer_id' => $customer->id,
            'customer_email' => $user->email,
            'total_amount' => 75.00,
            'order_date' => now()->toDateString(),
            'payment_status' => 'paid',
            'status' => 'delivered',
            'shipping_status' => 'delivered',
        ]);
        return [$user, $order];
    }

    private function makeReturn(User $user, Order $order, array $overrides = []): ReturnRequest
    {
        return ReturnRequest::create(array_merge([
            'order_id' => $order->id,
            'customer_id' => $user->id,
            'reason' => 'defective',
            'description' => 'Item arrived broken',
            'status' => 'pending',
        ], $overrides));
    }

    public function test_rma_number_auto_generated(): void
    {
        [$user, $order] = $this->makeOrderAndUser();
        $return = $this->makeReturn($user, $order);

        $this->assertStringStartsWith('RMA-', $return->rma_number);
    }

    public function test_approve_sets_status_to_approved(): void
    {
        [$user, $order] = $this->makeOrderAndUser();
        $return = $this->makeReturn($user, $order);

        $return->approve($user->id);

        $fresh = $return->fresh();
        $this->assertEquals('approved', $fresh->status);
        $this->assertEquals($user->id, $fresh->approved_by);
        $this->assertNotNull($fresh->approved_at);
    }

    public function test_mark_as_received_sets_status(): void
    {
        [$user, $order] = $this->makeOrderAndUser();
        $return = $this->makeReturn($user, $order, ['status' => 'approved']);

        $return->markAsReceived();

        $fresh = $return->fresh();
        $this->assertEquals('received', $fresh->status);
        $this->assertNotNull($fresh->received_at);
    }

    public function test_belongs_to_order(): void
    {
        [$user, $order] = $this->makeOrderAndUser();
        $return = $this->makeReturn($user, $order);

        $this->assertInstanceOf(Order::class, $return->order);
        $this->assertEquals($order->id, $return->order->id);
    }

    public function test_customer_relationship(): void
    {
        [$user, $order] = $this->makeOrderAndUser();
        $return = $this->makeReturn($user, $order);

        $this->assertInstanceOf(User::class, $return->customer);
        $this->assertEquals($user->id, $return->customer->id);
    }

    public function test_datetime_casts(): void
    {
        [$user, $order] = $this->makeOrderAndUser();
        $return = $this->makeReturn($user, $order, [
            'approved_at' => now(),
            'received_at' => now()->addDay(),
        ]);
        $return->save();

        $fresh = $return->fresh();
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $fresh->approved_at);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $fresh->received_at);
    }
}
