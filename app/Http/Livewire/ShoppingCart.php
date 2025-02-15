<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Session;
use App\Models\Product;

class ShoppingCart extends Component
{
    public $items = [];
    protected $listeners = ['addToCart'];

    public function mount()
    {
        $this->items = Session::get('cart', []);
    }

    public function render()
    {
        return view('livewire.shopping-cart', [
            'items' => $this->items,
            'total' => $this->calculateTotal(),
            'hasPhysicalProducts' => $this->hasPhysicalProducts()
        ]);
    }

    public function addToCart($productId, $name, $price, $quantity = 1)
    {
        $product = Product::findOrFail($productId);
        
        if (isset($this->items[$productId])) {
            $this->items[$productId]['quantity'] += $quantity;
        } else {
            $this->items[$productId] = [
                'name' => $name,
                'price' => $price,
                'quantity' => $quantity,
                'is_downloadable' => $product->is_downloadable,
            ];
        }

        Session::put('cart', $this->items);
        $this->emit('cartUpdated');
    }

    public function hasPhysicalProducts()
    {
        foreach ($this->items as $item) {
            if (!$item['is_downloadable']) {
                return true;
            }
        }
        return false;
    }

    public function updateQuantity($productId, $quantity)
    {
        if (!isset($this->items[$productId])) {
            $this->addError('product', 'Product not found in cart');
            return;
        }

        if (!is_int($quantity) || $quantity < 1) {
            $this->addError('quantity', 'Quantity must be a positive integer');
            return;
        }

        $this->items[$productId]['quantity'] = $quantity;
        Session::put('cart', $this->items);
        $this->emit('cartUpdated');
    }

    public function removeItem($productId)
    {
        if (isset($this->items[$productId])) {
            unset($this->items[$productId]);
            Session::put('cart', $this->items);
        }
    }

    public function clearCart()
    {
        $this->items = [];
        Session::forget('cart');
    }

    public function calculateTotal()
    {
        $total = 0;
        foreach ($this->items as $item) {
            $total += $item['price'] * $item['quantity'];
        }
        return round($total, 2);
    }
}