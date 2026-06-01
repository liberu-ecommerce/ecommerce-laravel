<?php

namespace App\Livewire;

use Livewire\Attributes\On;
use Livewire\Component;
use Illuminate\Support\Facades\Session;

class CartCount extends Component
{
    #[On('cartUpdated')]
    public function refresh(): void {}

    public function render()
    {
        $count = 0;
        $cart = Session::get('cart', []);

        foreach ($cart as $item) {
            $count += $item['quantity'];
        }

        return view('livewire.cart-count', [
            'count' => $count,
        ]);
    }
}
