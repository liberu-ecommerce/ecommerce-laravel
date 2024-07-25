<?php

namespace App\Filament\App\Resources\ProductRatingResource\Pages;

use App\Filament\App\Resources\ProductRatingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProductRating extends EditRecord
{
    protected static string $resource = ProductRatingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
