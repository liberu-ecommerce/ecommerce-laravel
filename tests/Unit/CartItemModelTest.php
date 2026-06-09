<?php

namespace Tests\Unit;

use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartItemModelTest extends TestCase
{
    use RefreshDatabase;

    private function makeProduct(): Product
    {
        $category = ProductCategory::create([
            'name' => 'Cart Cat',
            'slug' => 'cart-cat-' . uniqid(),
        ]);
        return Product::create([
            'name' => 'Cart Product',
            'slug' => 'cart-prod-' . uniqid(),
            'price' => 15.00,
            'category_id' => $category->id,
            'inventory_count' => 10,
        ]);
    }

    public function test_cart_item_can_be_created(): void
    {
        $user = User::factory()->create();
        $product = $this->makeProduct();

        $item = CartItem::create([
            'session_id' => 'sess_' . uniqid(),
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'price' => 15.00,
        ]);

        $this->assertInstanceOf(CartItem::class, $item);
        $this->assertEquals(2, $item->quantity);
    }

    public function test_has_product_id_foreign_key(): void
    {
        $user = User::factory()->create();
        $product = $this->makeProduct();

        $item = CartItem::create([
            'session_id' => 'sess_' . uniqid(),
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => 15.00,
        ]);

        $this->assertEquals($product->id, $item->product_id);
        $this->assertInstanceOf(Product::class, Product::find($item->product_id));
    }

    public function test_multiple_items_for_same_user(): void
    {
        $user = User::factory()->create();
        $product1 = $this->makeProduct();
        $product2 = $this->makeProduct();

        CartItem::create(['session_id' => 'sess_' . uniqid(), 'user_id' => $user->id, 'product_id' => $product1->id, 'quantity' => 1, 'price' => 10.00]);
        CartItem::create(['session_id' => 'sess_' . uniqid(), 'user_id' => $user->id, 'product_id' => $product2->id, 'quantity' => 3, 'price' => 25.00]);

        $this->assertCount(2, CartItem::where('user_id', $user->id)->get());
    }
}
