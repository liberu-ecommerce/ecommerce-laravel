<?php

namespace App\Filament\App\Resources\DocumentResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\App\Resources\DocumentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDocuments extends ListRecords
{
    protected static string $resource = DocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
