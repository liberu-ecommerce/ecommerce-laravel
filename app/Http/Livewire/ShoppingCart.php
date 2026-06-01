<?php

namespace App\Http\Livewire;

use Livewire\Attributes\On;
use Livewire\Component;
use Illuminate\Support\Facades\Session;
use App\Models\Product;

class ShoppingCart extends Component
{
    public array $items = [];

    public function mount(): void
    {
        $this->items = Session::get('cart', []);
    }

    public function render()
    {
        return view('livewire.shopping-cart', [
            'items' => $this->items,
            'total' => $this->calculateTotal(),
            'hasPhysicalProducts' => $this->hasPhysicalProducts(),
            'canCheckout' => count($this->items) > 0,
        ]);
    }

    #[On('addToCart')]
    public function addToCart(int $productId, string $name, float $price, int $quantity = 1, bool $isDownloadable = false, float $weight = 0): void
    {
        $product = Product::findOrFail($productId);

        if (!$isDownloadable && $product->inventory_count < $quantity) {
            session()->flash('error', 'Not enough inventory available.');
            return;
        }

        if (isset($this->items[$productId])) {
            $newQuantity = $this->items[$productId]['quantity'] + $quantity;
            if (!$isDownloadable && $newQuantity > $product->inventory_count) {
                session()->flash('error', 'Cannot add more items than available in stock.');
                return;
            }
            $this->items[$productId]['quantity'] = $newQuantity;
        } else {
            $this->items[$productId] = [
                'name' => $name,
                'price' => $price,
                'quantity' => $quantity,
                'is_downloadable' => $isDownloadable,
                'weight' => $weight,
            ];
        }

        Session::put('cart', $this->items);
        $this->dispatch('cartUpdated');
        session()->flash('success', 'Product added to cart successfully!');
    }

    public function hasPhysicalProducts(): bool
    {
        foreach ($this->items as $item) {
            if (!$item['is_downloadable']) {
                return true;
            }
        }
        return false;
    }

    public function updateQuantity(int $productId, int $quantity): void
    {
        if ($quantity < 1) {
            $this->addError('quantity', 'Quantity must be at least 1');
            return;
        }

        if (!isset($this->items[$productId])) {
            $this->addError('product', 'Product not found in cart');
            return;
        }

        $isDownloadable = $this->items[$productId]['is_downloadable'] ?? false;
        if (!$isDownloadable) {
            $product = Product::find($productId);
            if (!$product) {
                $this->addError('product', 'Product not found');
                return;
            }
            if ($quantity > $product->inventory_count) {
                session()->flash('error', 'Requested quantity exceeds available stock.');
                return;
            }
        }

        $this->items[$productId]['quantity'] = $quantity;
        Session::put('cart', $this->items);
        $this->dispatch('cartUpdated');
        session()->flash('success', 'Cart updated');
    }

    public function removeItem(int $productId): void
    {
        if (isset($this->items[$productId])) {
            unset($this->items[$productId]);
            Session::put('cart', $this->items);
            $this->dispatch('cartUpdated');
            session()->flash('success', 'Item removed from cart');
        }
    }

    public function clearCart(): void
    {
        $this->items = [];
        Session::forget('cart');
        $this->dispatch('cartUpdated');
        session()->flash('success', 'Cart cleared');
    }

    public function calculateTotal(): float
    {
        return round(array_reduce(
            $this->items,
            fn(float $carry, array $item) => $carry + ($item['price'] * $item['quantity']),
            0.0
        ), 2);
    }
}
