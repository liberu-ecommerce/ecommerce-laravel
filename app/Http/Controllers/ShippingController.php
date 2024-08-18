<?php

namespace App\Http\Controllers;

use App\Models\ShippingMethod;
use Illuminate\Http\Request;

class ShippingController extends Controller
{
    public function index()
    {
        $shippingMethods = ShippingMethod::all();
        return view('shipping.index', compact('shippingMethods'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'estimated_delivery_time' => 'required|string|max:255',
        ]);

        ShippingMethod::create($validatedData);

        return redirect()->route('shipping.index')->with('success', 'Shipping method created successfully.');
    }

    public function update(Request $request, ShippingMethod $shippingMethod)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'estimated_delivery_time' => 'required|string|max:255',
        ]);

        $shippingMethod->update($validatedData);

        return redirect()->route('shipping.index')->with('success', 'Shipping method updated successfully.');
    }

    public function destroy(ShippingMethod $shippingMethod)
    {
        $shippingMethod->delete();

        return redirect()->route('shipping.index')->with('success', 'Shipping method deleted successfully.');
    }
}