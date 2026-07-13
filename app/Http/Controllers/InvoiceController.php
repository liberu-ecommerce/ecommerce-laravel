<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    public function createInvoiceForOrder(int $orderId): Invoice
    {
        return Invoice::generateForOrder(Order::findOrFail($orderId));
    }

    public function index(Request $request): View
    {
        $query = Invoice::query();

        // Customers see only their own invoices (via the owning order); staff see all.
        if (! $request->user()?->hasRole(['super_admin', 'admin'])) {
            $query->whereHas('order', fn ($q) => $q->where('user_id', $request->user()->id));
        }

        if ($request->has('date')) {
            $query->whereDate('invoice_date', $request->date);
        }
        if ($request->has('status')) {
            $query->where('payment_status', $request->status);
        }
        $invoices = $query->latest()->paginate(10);

        return view('invoices.index', compact('invoices'));
    }

    public function show(Request $request, int $id): View
    {
        $query = Invoice::with(['order', 'order.items']);

        // Scope to the owner unless staff — a foreign id 404s instead of leaking PII.
        if (! $request->user()?->hasRole(['super_admin', 'admin'])) {
            $query->whereHas('order', fn ($q) => $q->where('user_id', $request->user()->id));
        }

        $invoice = $query->findOrFail($id);

        return view('invoices.show', compact('invoice'));
    }
}
