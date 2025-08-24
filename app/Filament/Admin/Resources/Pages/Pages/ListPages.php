<?php

namespace App\Filament\Admin\Resources\Pages\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Admin\Resources\Pages\PageResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPages extends ListRecords
{
    protected static string $resource = PageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
