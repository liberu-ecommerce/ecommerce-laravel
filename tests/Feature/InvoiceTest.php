&lt;?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use App\Models\Order;
use App\Models\Invoice;
use App\Mail\InvoiceMail;

class InvoiceTest extends TestCase
{
    use RefreshDatabase;

    public function testAutomaticInvoiceGeneration()
    {
        // Arrange: Simulate order completion
        $order = Order::factory()->create();

        // Act: Trigger invoice generation
        $response = $this->postJson('/api/orders/'.$order->id.'/complete');

        // Assert: Invoice is automatically generated with correct details
        $this->assertDatabaseHas('invoices', [
            'order_id' => $order->id,
        ]);
    }

    public function testInvoiceManagementInterfaceAccessibility()
    {
        // Act: Access the invoice management interface
        $response = $this->get('/invoices');

        // Assert: Interface is accessible
        $response->assertStatus(200);
    }

    public function testCorrectnessOfDetailedInvoiceView()
    {
        // Arrange: Create an invoice
        $invoice = Invoice::factory()->create();

        // Act: View the invoice
        $response = $this->get('/invoices/'.$invoice->id);

        // Assert: Response contains correct invoice details
        $response->assertSee($invoice->total_amount);
    }

    public function testSuccessfulPDFGeneration()
    {
        // Arrange: Create an invoice
        $invoice = Invoice::factory()->create();

        // Act: Request PDF generation
        $response = $this->get('/invoices/'.$invoice->id.'/pdf');

        // Assert: PDF is successfully generated
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    public function testEmailSendingWithInvoiceAttached()
    {
        // Arrange: Mock the mailer
        Mail::fake();
        $invoice = Invoice::factory()->create();

        // Act: Trigger email sending
        $this->post('/invoices/'.$invoice->id.'/send');

        // Assert: Email is sent with the correct attachment
        Mail::assertSent(InvoiceMail::class, function ($mail) use ($invoice) {
            return $mail->hasTo($invoice->customer->email) &&
                   $mail->attachments->contains(function ($attachment) use ($invoice) {
                       return $attachment->as == "invoice-{$invoice->id}.pdf";
                   });
        });
    }
}
