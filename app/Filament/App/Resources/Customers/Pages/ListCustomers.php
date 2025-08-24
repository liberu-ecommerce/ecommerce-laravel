<?php

namespace App\Filament\App\Resources\CustomerResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\App\Resources\CustomerResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCustomers extends ListRecords
{
    protected static string $resource = CustomerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
