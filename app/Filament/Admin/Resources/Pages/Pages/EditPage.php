<?php

namespace App\Filament\Admin\Resources\Pages\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Admin\Resources\Pages\PageResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPage extends EditRecord
{
    protected static string $resource = PageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
