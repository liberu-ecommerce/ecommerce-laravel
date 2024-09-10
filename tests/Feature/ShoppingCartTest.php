<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Http\Livewire\ShoppingCart;
use Livewire\Livewire;
use Illuminate\Support\Facades\Session;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ShoppingCartTest extends TestCase
{
    use RefreshDatabase;

    public function test_add_single_item_to_cart()
    {
        $product = Product::factory()->create(['price' => 9.99]);
        Livewire::test(ShoppingCart::class)
            ->call('addToCart', $product->id, $product->name, $product->price)
            ->assertSessionHas('cart', function ($cart) use ($product) {
                return $cart[$product->id]['quantity'] === 1 && $cart[$product->id]['price'] === 9.99;
            });
    }

    public function test_add_multiple_items_to_cart()
    {
        $product1 = Product::factory()->create(['price' => 5.00]);
        $product2 = Product::factory()->create(['price' => 15.00]);
        Livewire::test(ShoppingCart::class)
            ->call('addToCart', $product1->id, $product1->name, $product1->price)
            ->call('addToCart', $product2->id, $product2->name, $product2->price)
            ->assertSessionHas('cart', function ($cart) use ($product1, $product2) {
                return isset($cart[$product1->id], $cart[$product2->id]);
            });
    }

    public function test_update_item_quantity_in_cart()
    {
        $product = Product::factory()->create();
        Livewire::test(ShoppingCart::class)
            ->call('addToCart', $product->id, $product->name, $product->price)
            ->call('updateQuantity', $product->id, 3)
            ->assertSessionHas('cart', function ($cart) use ($product) {
                return $cart[$product->id]['quantity'] === 3;
            });
    }

    public function test_remove_item_from_cart()
    {
        $product = Product::factory()->create();
        Livewire::test(ShoppingCart::class)
            ->call('addToCart', $product->id, $product->name, $product->price)
            ->call('removeItem', $product->id)
            ->assertSessionMissing('cart', function ($cart) use ($product) {
                return isset($cart[$product->id]);
            });
    }

    public function test_cart_persistence_across_sessions()
    {
        $product = Product::factory()->create();
        Session::put('cart', [$product->id => ['name' => $product->name, 'price' => $product->price, 'quantity' => 1]]);
        $this->startSession();
        Livewire::test(ShoppingCart::class)
            ->assertSessionHas('cart', function ($cart) use ($product) {
                return isset($cart[$product->id]);
            });
    }

    public function test_cart_summary_and_totals()
    {
        $product = Product::factory()->create(['price' => 10.00]);
        Livewire::test(ShoppingCart::class)
            ->call('addToCart', $product->id, $product->name, $product->price, 2)
            ->assertViewHas('items', function ($items) use ($product) {
                $totalPrice = array_reduce($items, function ($carry, $item) {
                    return $carry + ($item['price'] * $item['quantity']);
                }, 0);
                return $totalPrice === 20.00 && count($items) === 1;
            });
    }

    public function test_add_item_with_invalid_price()
    {
        $product = Product::factory()->create();
        Livewire::test(ShoppingCart::class)
            ->call('addToCart', $product->id, $product->name, -10)
            ->assertHasErrors(['price' => 'Invalid price'])
            ->assertSessionMissing('cart');
    }

    public function test_add_item_with_invalid_quantity()
    {
        $product = Product::factory()->create();
        Livewire::test(ShoppingCart::class)
            ->call('addToCart', $product->id, $product->name, $product->price, 0)
            ->assertHasErrors(['quantity' => 'Quantity must be a positive integer'])
            ->assertSessionMissing('cart');
    }

    public function test_update_quantity_with_invalid_value()
    {
        $product = Product::factory()->create();
        Livewire::test(ShoppingCart::class)
            ->call('addToCart', $product->id, $product->name, $product->price)
            ->call('updateQuantity', $product->id, -1)
            ->assertHasErrors(['quantity' => 'Quantity must be a positive integer'])
            ->assertSessionHas('cart', function ($cart) use ($product) {
                return $cart[$product->id]['quantity'] === 1;
            });
    }

    public function test_update_quantity_for_non_existent_product()
    {
        Livewire::test(ShoppingCart::class)
            ->call('updateQuantity', 999, 1)
            ->assertHasErrors(['product' => 'Product not found in cart']);
    }

    public function test_calculate_total()
    {
        $product1 = Product::factory()->create(['price' => 10.00]);
        $product2 = Product::factory()->create(['price' => 15.50]);
        $component = Livewire::test(ShoppingCart::class)
            ->call('addToCart', $product1->id, $product1->name, $product1->price, 2)
            ->call('addToCart', $product2->id, $product2->name, $product2->price, 3);

        $this->assertEquals(66.50, $component->instance()->calculateTotal());
    }
}
