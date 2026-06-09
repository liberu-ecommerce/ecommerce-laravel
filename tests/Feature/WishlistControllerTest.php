<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WishlistControllerTest extends TestCase
{
    use RefreshDatabase;

    private function makeProduct(array $overrides = []): Product
    {
        $category = ProductCategory::create([
            'name' => 'Wishlist Category',
            'slug' => 'wish-cat-' . uniqid(),
        ]);

        return Product::create(array_merge([
            'name' => 'Wishlist Product',
            'slug' => 'wish-prod-' . uniqid(),
            'price' => 49.99,
            'category_id' => $category->id,
            'inventory_count' => 10,
        ], $overrides));
    }

    public function test_wishlist_index_requires_auth(): void
    {
        $response = $this->get(route('wishlist.index'));

        $response->assertRedirect();
    }

    public function test_authenticated_user_can_view_wishlist(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('wishlist.index'));

        $response->assertStatus(200);
    }

    public function test_add_product_to_wishlist(): void
    {
        $user = User::factory()->create();
        $product = $this->makeProduct();

        $response = $this->actingAs($user)->post(route('wishlist.add', $product));

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('wishlists', [
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);
    }

    public function test_add_product_twice_does_not_duplicate(): void
    {
        $user = User::factory()->create();
        $product = $this->makeProduct();

        $this->actingAs($user)->post(route('wishlist.add', $product));
        $this->actingAs($user)->post(route('wishlist.add', $product));

        $this->assertEquals(1, Wishlist::where('user_id', $user->id)->count());
    }

    public function test_remove_product_from_wishlist(): void
    {
        $user = User::factory()->create();
        $product = $this->makeProduct();

        Wishlist::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        $response = $this->actingAs($user)->delete(route('wishlist.remove', $product));

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('wishlists', [
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);
    }

    public function test_share_wishlist_generates_token(): void
    {
        $user = User::factory()->create();
        $product = $this->makeProduct();

        Wishlist::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        $response = $this->actingAs($user)->post(route('wishlist.share'));

        $response->assertRedirect(route('wishlist.index'));
        $this->assertDatabaseHas('wishlists', [
            'user_id' => $user->id,
        ]);
        $this->assertNotNull(
            Wishlist::where('user_id', $user->id)->value('share_token')
        );
    }

    public function test_shared_wishlist_is_accessible_without_auth(): void
    {
        $user = User::factory()->create();
        $product = $this->makeProduct();

        Wishlist::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'share_token' => 'public-share-token',
        ]);

        $response = $this->get(route('wishlist.shared', 'public-share-token'));

        $response->assertStatus(200);
    }

    public function test_shared_wishlist_with_invalid_token_returns_empty(): void
    {
        $response = $this->get(route('wishlist.shared', 'nonexistent-token'));

        $response->assertStatus(200);
    }
}
