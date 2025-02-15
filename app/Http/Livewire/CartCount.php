<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Session;

class CartCount extends Component
{
    protected $listeners = ['cartUpdated' => 'render'];

    public function render()
    {
        $count = 0;
        $cart = Session::get('cart', []);
        
        foreach ($cart as $item) {
            $count += $item['quantity'];
        }
        
        return view('livewire.cart-count', [
            'count' => $count
        ]);
    }
}