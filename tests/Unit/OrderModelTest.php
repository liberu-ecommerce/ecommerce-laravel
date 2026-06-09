<?php

namespace Tests\Unit;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductCategory;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderModelTest extends TestCase
{
    use RefreshDatabase;

    private function makeCustomer(): Customer
    {
        return Customer::create([
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'jane@example.com',
            'phone_number' => '555-9999',
            'address' => '456 Oak Ave',
            'city' => 'Springfield',
            'state' => 'IL',
            'postal_code' => '62701',
        ]);
    }

    private function makeOrder(array $overrides = []): Order
    {
        $customer = $this->makeCustomer();

        return Order::create(array_merge([
            'customer_id' => $customer->id,
            'customer_email' => 'jane@example.com',
            'order_date' => now()->toDateString(),
            'total_amount' => 100.00,
            'payment_status' => 'pending',
            'shipping_status' => 'pending',
            'status' => 'pending',
        ], $overrides));
    }

    public function test_order_can_be_created(): void
    {
        $order = $this->makeOrder();

        $this->assertInstanceOf(Order::class, $order);
        $this->assertDatabaseHas('orders', ['customer_email' => 'jane@example.com']);
    }

    public function test_order_belongs_to_customer(): void
    {
        $order = $this->makeOrder();

        $this->assertInstanceOf(Customer::class, $order->customer);
    }

    public function test_order_has_many_items(): void
    {
        $order = $this->makeOrder();
        $category = ProductCategory::create(['name' => 'Test', 'slug' => 'test-cat']);
        $product = Product::create([
            'name' => 'Test Product',
            'slug' => 'test-prod',
            'price' => 50.00,
            'category_id' => $category->id,
            'inventory_count' => 10,
        ]);

        $order->items()->create([
            'product_id' => $product->id,
            'quantity' => 2,
            'price' => 50.00,
        ]);

        $this->assertCount(1, $order->items);
    }

    public function test_order_casts_total_amount_to_decimal(): void
    {
        $order = $this->makeOrder(['total_amount' => 99.99]);

        $this->assertEquals('99.99', $order->total_amount);
    }

    public function test_order_is_dropshipped_cast_to_boolean(): void
    {
        $order = $this->makeOrder(['is_dropshipped' => true]);

        $this->assertTrue($order->is_dropshipped);
    }

    public function test_order_supplier_response_cast_to_array(): void
    {
        $order = $this->makeOrder(['supplier_response' => ['status' => 'ok', 'id' => '123']]);

        $this->assertIsArray($order->supplier_response);
        $this->assertEquals('ok', $order->supplier_response['status']);
    }
}
