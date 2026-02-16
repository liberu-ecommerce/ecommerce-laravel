<?php

namespace Tests\Unit;

use App\Models\GiftRegistry;
use App\Models\GiftRegistryItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GiftRegistryTest extends TestCase
{
    use RefreshDatabase;

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
