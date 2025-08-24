<?php

namespace App\Filament\App\Resources\Invoices\Pages;

use App\Filament\App\Resources\Invoices\InvoiceResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateInvoice extends CreateRecord
{
    protected static string $resource = InvoiceResource::class;
}
