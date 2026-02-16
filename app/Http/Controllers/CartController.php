<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\CouponService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class CartController extends Controller
{
    public function add(Request $request, Product $product)
    {
        $quantity = $request->input('quantity', 1);
        
        if ($product->inventory_count < $quantity) {
            return redirect()->back()->with('error', 'Not enough inventory available.');
        }

        $cart = Session::get('cart', []);
        
        if (isset($cart[$product->id])) {
            $cart[$product->id]['quantity'] += $quantity;
        } else {
            $cart[$product->id] = [
                'name' => $product->name,
                'price' => $product->price,
                'quantity' => $quantity,
                'is_downloadable' => $product->is_downloadable,
            ];
        }

        Session::put('cart', $cart);
        
        return redirect()->back()->with('success', 'Product added to cart successfully!');
    }

    public function index()
    {
        return view('cart.index');
    }

    public function update(Request $request, $productId)
    {
        $quantity = $request->input('quantity', 1);
        
        if ($quantity < 1) {
            return redirect()->back()->with('error', 'Quantity must be at least 1.');
        }

        $cart = Session::get('cart', []);
        
        if (!isset($cart[$productId])) {
            return redirect()->back()->with('error', 'Product not found in cart.');
        }

        $product = Product::find($productId);
        if (!$product) {
            return redirect()->back()->with('error', 'Product not found.');
        }

        if ($product->inventory_count < $quantity) {
            return redirect()->back()->with('error', 'Not enough inventory available.');
        }

        $cart[$productId]['quantity'] = $quantity;
        Session::put('cart', $cart);
        
        return redirect()->back()->with('success', 'Cart updated successfully!');
    }

    public function remove($productId)
    {
        $cart = Session::get('cart', []);
        
        if (isset($cart[$productId])) {
            unset($cart[$productId]);
            Session::put('cart', $cart);
            return redirect()->back()->with('success', 'Product removed from cart successfully!');
        }
        
        return redirect()->back()->with('error', 'Product not found in cart.');
    }

    public function clear()
    {
        Session::forget('cart');
        Session::forget('coupon');
        return redirect()->back()->with('success', 'Cart cleared successfully!');
    }

    public function applyCoupon(Request $request, CouponService $couponService)
    {
        $request->validate([
            'coupon_code' => 'required|string',
        ]);

        $cart = Session::get('cart', []);
        if (empty($cart)) {
            return redirect()->back()->with('error', 'Your cart is empty.');
        }

        $subtotal = collect($cart)->sum(function ($item) {
            return $item['price'] * $item['quantity'];
        });

        $result = $couponService->validateAndApplyCoupon($request->coupon_code, $subtotal);

        if ($result['valid']) {
            Session::put('coupon', [
                'code' => $request->coupon_code,
                'discount' => $result['discount'],
                'coupon_id' => $result['coupon']->id,
            ]);
            return redirect()->back()->with('success', $result['message']);
        }

        return redirect()->back()->with('error', $result['error']);
    }

    public function removeCoupon()
    {
        Session::forget('coupon');
        return redirect()->back()->with('success', 'Coupon removed successfully!');
    }
}