<?php

namespace App\Filament\App\Resources\Products\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\App\Resources\Products\ProductResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
