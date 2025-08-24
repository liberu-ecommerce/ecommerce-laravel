<?php

namespace App\Filament\App\Resources\ProductReviews\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\App\Resources\ProductReviews\ProductReviewResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProductReview extends EditRecord
{
    protected static string $resource = ProductReviewResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
