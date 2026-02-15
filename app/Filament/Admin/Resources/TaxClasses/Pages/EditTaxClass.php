<?php

namespace App\Filament\Admin\Resources\TaxClasses\Pages;

use App\Filament\Admin\Resources\TaxClasses\TaxClassResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTaxClass extends EditRecord
{
    protected static string $resource = TaxClassResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
