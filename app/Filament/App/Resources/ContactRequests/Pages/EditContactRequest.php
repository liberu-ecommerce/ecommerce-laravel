<?php

namespace App\Filament\App\Resources\ContactRequests\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\App\Resources\ContactRequests\ContactRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditContactRequest extends EditRecord
{
    protected static string $resource = ContactRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
