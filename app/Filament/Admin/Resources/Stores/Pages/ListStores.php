<?php

namespace App\Filament\Admin\Resources\Stores\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Admin\Resources\Stores\StoreResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListStores extends ListRecords
{
    protected static string $resource = StoreResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
