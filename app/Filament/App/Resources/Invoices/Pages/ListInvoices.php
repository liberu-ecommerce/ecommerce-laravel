<?php

namespace App\Filament\App\Resources\Invoices\Pages;

use Filament\Actions\CreateAction;
use App\Filament\App\Resources\Invoices\InvoiceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListInvoices extends ListRecords
{
    protected static string $resource = InvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
