<?php

namespace App\Filament\Admin\Resources\Menus\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Admin\Resources\Menus\MenuResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMenu extends EditRecord
{
    protected static string $resource = MenuResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
