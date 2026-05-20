<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;

class OrderHistoryController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $orders = Order::where('customer_id', $user->id)
                       ->orderBy('created_at', 'desc')
                       ->paginate(10);

        return view('orders.history', compact('orders'));
    }

    public function show($id)
    {
        $order = Order::findOrFail($id);

        // Ensure the order belongs to the authenticated user
        if ($order->customer_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        return view('orders.show', compact('order'));
    }
}