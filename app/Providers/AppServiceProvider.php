<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use App\Http\Livewire\CartCount;
use App\Http\Livewire\ShoppingCart;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        Livewire::component('cart-count', CartCount::class);
        Livewire::component('shopping-cart', ShoppingCart::class);
    }
}