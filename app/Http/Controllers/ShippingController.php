<?php

namespace App\Http\Controllers;

use App\Models\ShippingMethod;
use Illuminate\Http\Request;

class ShippingController extends Controller
{
    /**
     * Shipping methods are store-wide checkout config — restrict every action to
     * staff (the `auth` middleware on the routes already blocks guests).
     */
    private function ensureAdmin(): void
    {
        abort_unless(auth()->user()?->hasRole(['super_admin', 'admin']), 403);
    }

    public function index()
    {
        $this->ensureAdmin();

        $shippingMethods = ShippingMethod::all();

        return view('shipping.index', compact('shippingMethods'));
    }

    public function store(Request $request)
    {
        $this->ensureAdmin();

        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'base_rate' => 'required|numeric|min:0',
            'weight_rate' => 'nullable|numeric|min:0',
            'max_weight' => 'nullable|numeric|min:0',
            'estimated_delivery_time' => 'required|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        $validatedData['is_active'] = $request->boolean('is_active');

        ShippingMethod::create($validatedData);

        return redirect()->route('shipping.index')->with('success', 'Shipping method created successfully.');
    }

    public function update(Request $request, ShippingMethod $shippingMethod)
    {
        $this->ensureAdmin();

        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'base_rate' => 'required|numeric|min:0',
            'weight_rate' => 'nullable|numeric|min:0',
            'max_weight' => 'nullable|numeric|min:0',
            'estimated_delivery_time' => 'required|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        $validatedData['is_active'] = $request->boolean('is_active');

        $shippingMethod->update($validatedData);

        return redirect()->route('shipping.index')->with('success', 'Shipping method updated successfully.');
    }

    public function destroy(ShippingMethod $shippingMethod)
    {
        $this->ensureAdmin();

        $shippingMethod->delete();

        return redirect()->route('shipping.index')->with('success', 'Shipping method deleted successfully.');
    }
}
