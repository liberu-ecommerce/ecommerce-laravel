<?php

namespace App\Filament\App\Resources\OrderResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\App\Resources\OrderResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
