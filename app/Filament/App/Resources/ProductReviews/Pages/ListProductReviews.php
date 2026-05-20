<?php

namespace App\Filament\App\Resources\ProductReviews\Pages;

use Filament\Actions\CreateAction;
use App\Filament\App\Resources\ProductReviews\ProductReviewResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProductReviews extends ListRecords
{
    protected static string $resource = ProductReviewResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
