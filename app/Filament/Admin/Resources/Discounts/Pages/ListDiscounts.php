<?php

namespace App\Filament\Admin\Resources\Discounts\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Admin\Resources\Discounts\DiscountResource;
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
