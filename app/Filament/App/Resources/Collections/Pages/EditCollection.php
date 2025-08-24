<?php

namespace App\Filament\App\Resources\Collections\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\App\Resources\Collections\CollectionResource;
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
