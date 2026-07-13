<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WishlistModelTest extends TestCase
{
    use RefreshDatabase;

    private function makeProduct(): Product
    {
        $category = ProductCategory::create([
            'name' => 'Wish Cat',
            'slug' => 'wish-cat-' . uniqid(),
        ]);
        return Product::create([
            'name' => 'Wish Product',
            'slug' => 'wish-prod-' . uniqid(),
            'price' => 15.00,
            'category_id' => $category->id,
            'inventory_count' => 5,
        ]);
    }

    public function test_wishlist_item_can_be_created(): void
    {
        $user = User::factory()->create();
        $product = $this->makeProduct();

        $item = Wishlist::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        $this->assertInstanceOf(Wishlist::class, $item);
        $this->assertEquals($user->id, $item->user_id);
        $this->assertEquals($product->id, $item->product_id);
    }

    public function test_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $product = $this->makeProduct();

        $item = Wishlist::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        $this->assertInstanceOf(User::class, $item->user);
        $this->assertEquals($user->id, $item->user->id);
    }

    public function test_belongs_to_product(): void
    {
        $user = User::factory()->create();
        $product = $this->makeProduct();

        $item = Wishlist::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        $this->assertInstanceOf(Product::class, $item->product);
        $this->assertEquals($product->id, $item->product->id);
    }
}
