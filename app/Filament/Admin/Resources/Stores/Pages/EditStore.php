<?php

namespace App\Filament\Admin\Resources\Stores\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Admin\Resources\Stores\StoreResource;
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
