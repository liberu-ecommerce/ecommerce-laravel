<?php

namespace App\Http\Controllers;

use Livewire\Livewire;
use App\Models\Invoice;
use App\Models\Order;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade as PDF;
use Illuminate\Support\Facades\Mail;
use App\Mail\InvoiceMail;

class InvoiceController extends Controller
{
    public function createInvoiceForOrder($orderId)
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
            $invoice->products()->attach($product->id, ['quantity' => $product->pivot->quantity, 'price' => $product->price]);
        }
        return $invoice;
    }
    
    public function getCouponUsageReport(Request $request)
    {
        $startDate = $request->input('start_date', now()->subMonth());
        $endDate = $request->input('end_date', now());
    
        $couponUsage = Coupon::withCount(['orders' => function ($query) use ($startDate, $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }])
        ->withSum(['orders' => function ($query) use ($startDate, $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }], 'total_amount')
        ->withSum(['orders' => function ($query) use ($startDate, $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }], 'discount_amount')
        ->get();
    
        return view('admin.reports.coupon-usage', compact('couponUsage', 'startDate', 'endDate'));
    }

    public function index(Request $request)
    {
        $query = Invoice::query();
        if ($request->has('date')) {
            $query->whereDate('invoice_date', $request->date);
        }
        if ($request->has('status')) {
            $query->where('payment_status', $request->status);
        }
        $invoices = $query->paginate(10);
        return response()->json($invoices);
    }

    public function show($id)
    {
        $invoice = Invoice::with(['order', 'order.products', 'customer'])->findOrFail($id);
        return response()->json($invoice);
    }

    public function downloadInvoiceAsPDF($id)
    {
        $invoice = Invoice::findOrFail($id);
        $pdf = PDF::loadView('invoices.pdf', compact('invoice'));
        return $pdf->download("invoice-{$id}.pdf");
    }

    public function sendInvoiceToCustomer($id)
    {
        $invoice = Invoice::with('customer')->findOrFail($id);
        $pdf = Livewire::mount('invoice-pdf', ['invoiceId' => $id])->httpResponse->getContent();
        Mail::to($invoice->customer->email)->send(new InvoiceMail($invoice, $pdf));
        return response()->json(['message' => 'Invoice sent to customer successfully.']);
    }
}
