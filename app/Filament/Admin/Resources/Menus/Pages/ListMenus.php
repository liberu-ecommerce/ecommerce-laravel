<?php

namespace App\Filament\Admin\Resources\Menus\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Admin\Resources\Menus\MenuResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMenus extends ListRecords
{
    protected static string $resource = MenuResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
