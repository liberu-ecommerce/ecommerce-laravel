<?php

namespace App\Filament\Admin\Resources\TaxClasses\Pages;

use App\Filament\Admin\Resources\TaxClasses\TaxClassResource;
use Filament\Resources\Pages\ListRecords;

class ListTaxClasses extends ListRecords
{
    protected static string $resource = TaxClassResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make(),
        ];
    }
}
