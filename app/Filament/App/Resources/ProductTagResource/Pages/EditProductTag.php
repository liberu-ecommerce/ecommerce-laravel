<?php

namespace App\Filament\App\Resources\ProductTagResource\Pages;

use App\Filament\App\Resources\ProductTagResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProductTag extends EditRecord
{
    protected static string $resource = ProductTagResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
