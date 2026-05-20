<?php

namespace App\Filament\App\Resources\ProductRatings\Pages;

use Filament\Actions\CreateAction;
use App\Filament\App\Resources\ProductRatings\ProductRatingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProductRatings extends ListRecords
{
    protected static string $resource = ProductRatingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
