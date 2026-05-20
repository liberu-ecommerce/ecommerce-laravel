<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Barryvdh\DomPDF\Facade as PDF;
use App\Models\Invoice;

class InvoicePdf extends Component
{
    public $invoiceId;

    public function mount($invoiceId)
    {
        $this->invoiceId = $invoiceId;
    }

    public function render()
    {
        $invoice = Invoice::findOrFail($this->invoiceId);
        $pdf = PDF::loadView('invoices.pdf', ['invoice' => $invoice]);
        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->stream();
        }, "invoice-{$this->invoiceId}.pdf");
    }
}
