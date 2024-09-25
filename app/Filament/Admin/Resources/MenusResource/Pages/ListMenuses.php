<?php

namespace App\Filament\Admin\Resources\MenusResource\Pages;

use App\Filament\Admin\Resources\MenusResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMenuses extends ListRecords
{
    protected static string $resource = MenusResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
