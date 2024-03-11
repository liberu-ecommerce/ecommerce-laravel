&lt;?php

namespace App\Mail;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Barryvdh\DomPDF\Facade as PDF;

class InvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    public $invoice;

    public function __construct(Invoice $invoice)
    {
        $this->invoice = $invoice;
    }

    public function build()
    {
        $pdf = PDF::loadView('invoices.pdf', ['invoice' => $this->invoice]);

        return $this->subject('Your Invoice from Ecommerce')
                    ->view('emails.invoice')
                    ->attachData($pdf->output(), "invoice-{$this->invoice->id}.pdf", [
                        'mime' => 'application/pdf',
                    ]);
    }
}
