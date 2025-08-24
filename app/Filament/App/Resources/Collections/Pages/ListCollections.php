<?php

namespace App\Filament\App\Resources\Collections\Pages;

use Filament\Actions\CreateAction;
use App\Filament\App\Resources\Collections\CollectionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCollections extends ListRecords
{
    protected static string $resource = CollectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
