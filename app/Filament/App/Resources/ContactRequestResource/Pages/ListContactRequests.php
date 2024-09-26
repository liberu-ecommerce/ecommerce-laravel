<?php

namespace App\Filament\App\Resources\ContactRequestResource\Pages;

use App\Filament\App\Resources\ContactRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListContactRequests extends ListRecords
{
    protected static string $resource = ContactRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
