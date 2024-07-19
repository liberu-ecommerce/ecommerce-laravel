<?php

namespace App\Filament\Admin\Resources\ProductRatingResource\Pages;

use App\Filament\Admin\Resources\ProductRatingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProductRatings extends ListRecords
{
    protected static string $resource = ProductRatingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
