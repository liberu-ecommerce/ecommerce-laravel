<?php

namespace App\Filament\Admin\Resources\DiscountResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Admin\Resources\DiscountResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDiscounts extends ListRecords
{
    protected static string $resource = DiscountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
