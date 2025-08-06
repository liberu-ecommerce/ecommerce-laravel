<?php

namespace App\Filament\Admin\Resources\StoreResource\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Admin\Resources\StoreResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditStore extends EditRecord
{
    protected static string $resource = StoreResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
