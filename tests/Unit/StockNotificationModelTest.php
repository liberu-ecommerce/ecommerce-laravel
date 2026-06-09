<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\StockNotification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockNotificationModelTest extends TestCase
{
    use RefreshDatabase;

    private function makeProduct(): Product
    {
        $cat = ProductCategory::create(['name' => 'SN Cat', 'slug' => 'sn-cat-' . uniqid()]);
        return Product::create([
            'name' => 'SN Product',
            'slug' => 'sn-prod-' . uniqid(),
            'price' => 30.00,
            'category_id' => $cat->id,
            'inventory_count' => 0,
        ]);
    }

    public function test_stock_notification_can_be_created(): void
    {
        $product = $this->makeProduct();
        $user = User::factory()->create();

        $notif = StockNotification::create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'email' => $user->email,
            'notified' => false,
        ]);

        $this->assertInstanceOf(StockNotification::class, $notif);
        $this->assertFalse($notif->notified);
    }

    public function test_mark_as_notified(): void
    {
        $product = $this->makeProduct();

        $notif = StockNotification::create([
            'product_id' => $product->id,
            'email' => 'test@example.com',
            'notified' => false,
        ]);

        $notif->markAsNotified();

        $fresh = $notif->fresh();
        $this->assertTrue($fresh->notified);
        $this->assertNotNull($fresh->notified_at);
    }

    public function test_get_pending_for_product(): void
    {
        $product = $this->makeProduct();

        StockNotification::create(['product_id' => $product->id, 'email' => 'a@test.com', 'notified' => false]);
        StockNotification::create(['product_id' => $product->id, 'email' => 'b@test.com', 'notified' => true]);

        $pending = StockNotification::getPendingForProduct($product->id);

        $this->assertCount(1, $pending);
        $this->assertEquals('a@test.com', $pending->first()->email);
    }

    public function test_notified_is_boolean_cast(): void
    {
        $product = $this->makeProduct();
        $notif = StockNotification::create([
            'product_id' => $product->id,
            'email' => 'cast@test.com',
            'notified' => false,
        ]);

        $this->assertIsBool($notif->fresh()->notified);
    }
}
