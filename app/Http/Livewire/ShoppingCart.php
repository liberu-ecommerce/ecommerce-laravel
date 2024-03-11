&lt;?php

namespace App\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Session;

class ShoppingCart extends Component
{
    public $items = [];

    public function mount()
    {
        $this->items = Session::get('cart', []);
    }

    public function render()
    {
        return view('livewire.shopping-cart', ['items' => $this->items]);
    }

    public function addToCart($productId, $name, $price, $quantity = 1)
    {
        if (isset($this->items[$productId])) {
            $this->items[$productId]['quantity'] += $quantity;
        } else {
            $this->items[$productId] = [
                'name' => $name,
                'price' => $price,
                'quantity' => $quantity,
            ];
        }

        Session::put('cart', $this->items);
    }

    public function updateQuantity($productId, $quantity)
    {
        if (isset($this->items[$productId]) && $quantity > 0) {
            $this->items[$productId]['quantity'] = $quantity;
            Session::put('cart', $this->items);
        }
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
}
