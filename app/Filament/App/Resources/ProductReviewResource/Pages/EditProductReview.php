<?php

namespace App\Filament\App\Resources\ProductReviewResource\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\App\Resources\ProductReviewResource;
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
