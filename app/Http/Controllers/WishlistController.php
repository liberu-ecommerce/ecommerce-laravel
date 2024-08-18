<?php

namespace App\Http\Controllers;

use App\Models\Wishlist;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class WishlistController extends Controller
{
    public function index()
    {
        $wishlist = auth()->user()->wishlist()->with('product')->get();
        return view('wishlist.index', compact('wishlist'));
    }

    public function add(Product $product)
    {
        $wishlist = Wishlist::firstOrCreate([
            'user_id' => auth()->id(),
            'product_id' => $product->id,
        ]);

        return redirect()->back()->with('success', 'Product added to wishlist');
    }

    public function remove(Product $product)
    {
        auth()->user()->wishlist()->where('product_id', $product->id)->delete();
        return redirect()->back()->with('success', 'Product removed from wishlist');
    }

    public function share()
    {
        $shareToken = Str::random(32);
        auth()->user()->wishlist()->update(['share_token' => $shareToken]);
        return redirect()->route('wishlist.index')->with('share_url', route('wishlist.shared', $shareToken));
    }

    public function sharedWishlist($shareToken)
    {
        $wishlist = Wishlist::where('share_token', $shareToken)->with('product')->get();
        return view('wishlist.shared', compact('wishlist'));
    }
}