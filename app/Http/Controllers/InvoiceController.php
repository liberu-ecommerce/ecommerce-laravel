<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Invoice;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    public function createInvoiceForOrder(int $orderId): Invoice
    {
        $order = Order::findOrFail($orderId);
        $invoice = Invoice::create([
            'order_id' => $order->id,
            'invoice_date' => now(),
            'total_amount' => $order->total,
            'coupon_id' => $order->coupon_id,
            'discount_amount' => $order->discount_amount,
        ]);
        foreach ($order->products as $product) {
            $invoice->products()->attach($product->id, [
                'quantity' => $product->pivot->quantity,
                'price' => $product->price,
            ]);
        }
        return $invoice;
    }

    public function index(Request $request): View
    {
        $query = Invoice::query();
        if ($request->has('date')) {
            $query->whereDate('invoice_date', $request->date);
        }
        if ($request->has('status')) {
            $query->where('payment_status', $request->status);
        }
        $invoices = $query->latest()->paginate(10);
        return view('invoices.index', compact('invoices'));
    }

    public function show(int $id): View
    {
        $invoice = Invoice::with(['order', 'order.items'])->findOrFail($id);
        return view('invoices.show', compact('invoice'));
    }
}
