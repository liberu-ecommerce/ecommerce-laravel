<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductInteraction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductInteractionModelTest extends TestCase
{
    use RefreshDatabase;

    private function makeProduct(): Product
    {
        $cat = ProductCategory::create(['name' => 'PI Cat', 'slug' => 'pi-cat-' . uniqid()]);
        return Product::create([
            'name' => 'PI Product',
            'slug' => 'pi-prod-' . uniqid(),
            'price' => 25.00,
            'category_id' => $cat->id,
            'inventory_count' => 5,
        ]);
    }

    public function test_product_interaction_can_be_created(): void
    {
        $user = User::factory()->create();
        $product = $this->makeProduct();

        $interaction = ProductInteraction::create([
            'user_id' => $user->id,
            'session_id' => 'sess_' . uniqid(),
            'product_id' => $product->id,
            'interaction_type' => 'view',
            'interacted_at' => now(),
        ]);

        $this->assertInstanceOf(ProductInteraction::class, $interaction);
        $this->assertEquals('view', $interaction->interaction_type);
    }

    public function test_metadata_is_array_cast(): void
    {
        $product = $this->makeProduct();

        $interaction = ProductInteraction::create([
            'session_id' => 'sess_' . uniqid(),
            'product_id' => $product->id,
            'interaction_type' => 'view',
            'metadata' => ['source' => 'search', 'position' => 3],
            'interacted_at' => now(),
        ]);

        $this->assertIsArray($interaction->fresh()->metadata);
        $this->assertEquals('search', $interaction->fresh()->metadata['source']);
    }

    public function test_belongs_to_product(): void
    {
        $product = $this->makeProduct();

        $interaction = ProductInteraction::create([
            'session_id' => 'sess_' . uniqid(),
            'product_id' => $product->id,
            'interaction_type' => 'add_to_cart', // valid enum value
            'interacted_at' => now(),
        ]);

        $this->assertInstanceOf(Product::class, $interaction->product);
        $this->assertEquals($product->id, $interaction->product->id);
    }
}
