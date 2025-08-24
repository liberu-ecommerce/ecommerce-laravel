<?php

namespace App\Filament\App\Resources\ProductReviewResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\App\Resources\ProductReviewResource;
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
