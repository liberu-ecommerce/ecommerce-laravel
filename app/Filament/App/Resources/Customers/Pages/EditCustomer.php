<?php

namespace App\Filament\App\Resources\CustomerResource\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\App\Resources\CustomerResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCustomer extends EditRecord
{
    protected static string $resource = CustomerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
