<?php

namespace Tests\Unit;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Refund;
use App\Models\RefundItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RefundItemModelTest extends TestCase
{
    use RefreshDatabase;

    private function makeOrderItem(): array
    {
        $user = User::factory()->create();
        $customer = Customer::create([
            'user_id' => $user->id,
            'first_name' => 'RI',
            'last_name' => 'Test',
            'email' => $user->email,
            'phone_number' => '555-0010',
            'address' => '10 RI St',
            'city' => 'Chicago',
            'state' => 'IL',
            'postal_code' => '60610',
        ]);
        $order = Order::create([
            'customer_id' => $customer->id,
            'customer_email' => $user->email,
            'total_amount' => 100.00,
            'order_date' => now()->toDateString(),
            'payment_status' => 'paid',
            'shipping_status' => 'delivered',
        ]);
        $cat = ProductCategory::create(['name' => 'RI Cat', 'slug' => 'ri-cat-' . uniqid()]);
        $product = Product::create([
            'name' => 'RI Product',
            'slug' => 'ri-prod-' . uniqid(),
            'price' => 50.00,
            'category_id' => $cat->id,
            'inventory_count' => 10,
        ]);
        $orderItem = OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'price' => 50.00,
        ]);

        $refund = Refund::create([
            'order_id' => $order->id,
            'amount' => 50.00,
            'status' => 'pending',
        ]);

        return [$orderItem, $refund];
    }

    public function test_refund_item_can_be_created(): void
    {
        [$orderItem, $refund] = $this->makeOrderItem();

        $item = RefundItem::create([
            'refund_id' => $refund->id,
            'order_item_id' => $orderItem->id,
            'quantity' => 1,
            'amount' => 50.00,
            'restock' => true,
        ]);

        $this->assertInstanceOf(RefundItem::class, $item);
        $this->assertEquals(1, $item->quantity);
    }

    public function test_restock_is_boolean_cast(): void
    {
        [$orderItem, $refund] = $this->makeOrderItem();

        $item = RefundItem::create([
            'refund_id' => $refund->id,
            'order_item_id' => $orderItem->id,
            'quantity' => 1,
            'amount' => 25.00,
            'restock' => true,
        ]);

        $this->assertIsBool($item->fresh()->restock);
        $this->assertTrue($item->fresh()->restock);
    }

    public function test_belongs_to_refund(): void
    {
        [$orderItem, $refund] = $this->makeOrderItem();

        $item = RefundItem::create([
            'refund_id' => $refund->id,
            'order_item_id' => $orderItem->id,
            'quantity' => 1,
            'amount' => 50.00,
        ]);

        $this->assertInstanceOf(Refund::class, $item->refund);
        $this->assertEquals($refund->id, $item->refund->id);
    }
}
