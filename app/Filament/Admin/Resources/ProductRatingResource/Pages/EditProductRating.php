<?php

namespace App\Filament\Admin\Resources\ProductRatingResource\Pages;

use App\Filament\Admin\Resources\ProductRatingResource;
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
