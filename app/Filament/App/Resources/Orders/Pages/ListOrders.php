<?php

namespace App\Filament\App\Resources\Orders\Pages;

use Filament\Actions\CreateAction;
use App\Filament\App\Resources\Orders\OrderResource;
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
