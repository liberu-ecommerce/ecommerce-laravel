<?php

namespace App\Filament\App\Resources\CollectionResource\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\App\Resources\CollectionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCollection extends EditRecord
{
    protected static string $resource = CollectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
