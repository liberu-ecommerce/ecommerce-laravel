<?php

namespace Tests\Feature;

use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use App\Services\CartService;
use Illuminate\Auth\Events\Login;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class CartPersistenceTest extends TestCase
{
    use RefreshDatabase;

    private function saved(User $user, Product $product, int $qty, float $price = 10): CartItem
    {
        return CartItem::create([
            'user_id' => $user->id,
            'session_id' => 'old-session',
            'product_id' => $product->id,
            'quantity' => $qty,
            'price' => $price,
        ]);
    }

    public function test_merge_combines_saved_and_session_carts(): void
    {
        $user = User::factory()->create();
        $saved = Product::factory()->create();
        $guest = Product::factory()->create();
        $this->saved($user, $saved, 2);
        Session::put('cart', [$guest->id => ['name' => $guest->name, 'price' => 5, 'quantity' => 1, 'is_downloadable' => false]]);

        app(CartService::class)->mergeIntoSession($user);

        $cart = Session::get('cart');
        $this->assertEquals(2, $cart[$saved->id]['quantity']);
        $this->assertEquals(1, $cart[$guest->id]['quantity']);
    }

    public function test_merge_sums_quantities_for_the_same_product(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $this->saved($user, $product, 2);
        Session::put('cart', [$product->id => ['name' => $product->name, 'price' => 10, 'quantity' => 3, 'is_downloadable' => false]]);

        app(CartService::class)->mergeIntoSession($user);

        $this->assertEquals(5, Session::get('cart')[$product->id]['quantity']);
    }

    public function test_login_event_merges_the_saved_cart_into_the_session(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $this->saved($user, $product, 4);

        event(new Login('web', $user, false));

        $this->assertEquals(4, Session::get('cart')[$product->id]['quantity']);
    }

    public function test_authenticated_add_persists_the_cart(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['inventory_count' => 10]);

        $this->actingAs($user)->post(route('cart.add', $product), ['quantity' => 2]);

        $this->assertDatabaseHas('cart_items', [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);
    }

    public function test_guest_add_does_not_persist(): void
    {
        $product = Product::factory()->create(['inventory_count' => 10]);

        $this->post(route('cart.add', $product), ['quantity' => 1]);

        $this->assertDatabaseCount('cart_items', 0);
    }
}
