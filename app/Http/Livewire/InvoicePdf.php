<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Invoice;

class InvoicePdf extends Component
{
    public int $invoiceId;
    public ?Invoice $invoice = null;

    public function mount(int $invoiceId): void
    {
        $this->invoiceId = $invoiceId;
        $this->invoice = Invoice::findOrFail($invoiceId);
    }

    public function render()
    {
        return view('livewire.invoice-pdf', [
            'invoice' => $this->invoice,
        ]);
    }
}
