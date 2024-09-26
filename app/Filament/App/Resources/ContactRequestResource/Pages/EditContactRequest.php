<?php

namespace App\Filament\App\Resources\ContactRequestResource\Pages;

use App\Filament\App\Resources\ContactRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditContactRequest extends EditRecord
{
    protected static string $resource = ContactRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
