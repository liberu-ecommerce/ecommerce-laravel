<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class OrderHistoryController extends Controller
{
    public function index()
    {
        // Order ownership is orders.user_id (set at authenticated checkout), not
        // customer_id (a FK to the unrelated customers table, never populated) —
        // scoping on customer_id made this list permanently empty.
        $orders = Auth::user()->orders()
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('orders.history', compact('orders'));
    }

    public function show($id)
    {
        // Scope the lookup to the user's own orders: a foreign or guest order is
        // simply not found (404), which is both the ownership check and the fix
        // for owners being 403'd off their own orders.
        $order = Auth::user()->orders()->findOrFail($id);

        return view('orders.show', compact('order'));
    }
}
