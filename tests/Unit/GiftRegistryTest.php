<?php

namespace Tests\Unit;

use App\Models\GiftRegistry;
use App\Models\GiftRegistryItem;
use App\Models\GiftRegistryPurchase;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GiftRegistryTest extends TestCase
{
    use RefreshDatabase;

    private function makeOrder(): Order
    {
        return Order::create([
            'customer_email' => 'buyer@example.com',
            'total_amount' => 100,
            'status' => 'pending',
        ]);
    }

    private function makeItem(int $requested = 3): GiftRegistryItem
    {
        $registry = GiftRegistry::factory()->create();
        $product = Product::factory()->create();

        return $registry->items()->create([
            'product_id' => $product->id,
            'quantity_requested' => $requested,
            'quantity_purchased' => 0,
        ]);
    }

    public function test_mark_purchased_records_purchase_and_updates_count()
    {
        $item = $this->makeItem(3);
        $order = $this->makeOrder();

        $purchase = $item->markPurchased(2, $order->id, 'Alice', 'alice@example.com');

        $this->assertInstanceOf(GiftRegistryPurchase::class, $purchase);
        $this->assertDatabaseHas('gift_registry_purchases', [
            'registry_item_id' => $item->id,
            'order_id' => $order->id,
            'quantity' => 2,
            'purchaser_name' => 'Alice',
        ]);

        $item->refresh();
        $this->assertEquals(2, $item->quantity_purchased);
        $this->assertEquals(1, $item->getRemainingQuantity());
        $this->assertFalse($item->isFullyPurchased());
    }

    public function test_purchases_relation_and_inverse_resolve()
    {
        $item = $this->makeItem(3);
        $order = $this->makeOrder();

        $purchase = $item->markPurchased(1, $order->id);

        $this->assertCount(1, $item->purchases()->get());
        $this->assertEquals($item->id, $purchase->registryItem->id);
    }

    public function test_registry_purchases_through_relation()
    {
        $item = $this->makeItem(3);
        $order = $this->makeOrder();

        $item->markPurchased(1, $order->id);

        $this->assertCount(1, $item->registry->purchases()->get());
    }

    public function test_mark_purchased_rejects_over_purchase()
    {
        $item = $this->makeItem(1);
        $order = $this->makeOrder();

        $this->expectException(\InvalidArgumentException::class);

        try {
            $item->markPurchased(2, $order->id);
        } finally {
            $this->assertEquals(0, $item->fresh()->quantity_purchased);
            $this->assertEquals(0, GiftRegistryPurchase::count());
        }
    }

    public function test_mark_purchased_rejects_non_positive_quantity()
    {
        $item = $this->makeItem(3);
        $order = $this->makeOrder();

        $this->expectException(\InvalidArgumentException::class);
        $item->markPurchased(0, $order->id);
    }

    public function test_mark_purchased_to_capacity_marks_fully_purchased()
    {
        $item = $this->makeItem(2);
        $order = $this->makeOrder();

        $item->markPurchased(2, $order->id);
        $item->refresh();

        $this->assertTrue($item->isFullyPurchased());
        $this->assertEquals(0, $item->getRemainingQuantity());
    }

    public function test_gift_registry_can_be_created()
    {
        $user = User::factory()->create();

        $registry = GiftRegistry::create([
            'user_id' => $user->id,
            'name' => 'John & Jane Wedding',
            'type' => 'wedding',
            'event_date' => '2025-06-15',
            'privacy' => 'public',
        ]);

        $this->assertDatabaseHas('gift_registries', [
            'user_id' => $user->id,
            'name' => 'John & Jane Wedding',
        ]);

        $this->assertNotNull($registry->slug);
    }

    public function test_registry_can_have_items()
    {
        $registry = GiftRegistry::factory()->create();
        $product = Product::factory()->create();

        $item = $registry->items()->create([
            'product_id' => $product->id,
            'quantity_requested' => 2,
            'quantity_purchased' => 0,
        ]);

        $this->assertEquals(2, $item->getRemainingQuantity());
    }

    public function test_registry_calculates_completion_percentage()
    {
        $registry = GiftRegistry::factory()->create();
        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create();

        $registry->items()->create([
            'product_id' => $product1->id,
            'quantity_requested' => 2,
            'quantity_purchased' => 2,
        ]);

        $registry->items()->create([
            'product_id' => $product2->id,
            'quantity_requested' => 2,
            'quantity_purchased' => 0,
        ]);

        $registry->refresh();
        $completion = $registry->getCompletionPercentage();

        $this->assertEquals(50.0, $completion);
    }

    public function test_private_registry_generates_access_code()
    {
        $user = User::factory()->create();

        $registry = GiftRegistry::create([
            'user_id' => $user->id,
            'name' => 'Private Registry',
            'type' => 'wedding',
            'privacy' => 'private',
        ]);

        $this->assertNotNull($registry->access_code);
        $this->assertEquals(8, strlen($registry->access_code));
    }
}
