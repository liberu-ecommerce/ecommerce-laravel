<?php

namespace App\Filament\App\Resources\SimpleProductResource\Pages;

use App\Filament\App\Resources\SimpleProductResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSimpleProducts extends ListRecords
{
    protected static string $resource = SimpleProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
