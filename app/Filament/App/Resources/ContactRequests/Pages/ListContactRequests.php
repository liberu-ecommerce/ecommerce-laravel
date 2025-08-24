<?php

namespace App\Filament\App\Resources\ContactRequests\Pages;

use Filament\Actions\CreateAction;
use App\Filament\App\Resources\ContactRequests\ContactRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListContactRequests extends ListRecords
{
    protected static string $resource = ContactRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
