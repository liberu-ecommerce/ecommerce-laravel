<?php

namespace App\Filament\Admin\Resources\SimpleProductResource\Pages;

use App\Filament\Admin\Resources\SimpleProductResource;
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
