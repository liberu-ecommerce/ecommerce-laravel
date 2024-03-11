<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Http\Livewire\InvoicePdf;
use App\Models\Invoice;
use Barryvdh\DomPDF\Facade as PDF;
use Illuminate\Http\Response;

class InvoicePdfTest extends TestCase
{
    public function testMountMethodSetsInvoiceId()
    {
        // Arrange
        $invoiceId = 1;

        // Act
        $component = new InvoicePdf();
        $component->mount($invoiceId);

        // Assert
        $this->assertEquals($invoiceId, $component->invoiceId);
    }

    public function testRenderMethodReturnsExpectedResponse()
    {
        // Arrange
        $invoiceId = 1;
        $mockInvoice = Invoice::factory()->make();
        $mockPdf = $this->createMock(PDF::class);
        $mockResponse = $this->createMock(Response::class);

        // Mock the PDF facade
        PDF::shouldReceive('loadView')->andReturn($mockPdf);
        $mockPdf->shouldReceive('stream')->andReturn('mock-pdf-content');

        // Mock the response helper function
        $this->app->bind('response', function () use ($mockResponse) {
            return $mockResponse;
        });

        // Act
        $component = new InvoicePdf();
        $component->invoiceId = $invoiceId;
        $response = $component->render();

        // Assert
        $this->assertSame($mockResponse, $response);
    }
}
