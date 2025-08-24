<?php

namespace App\Filament\App\Resources\OrderResource\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\App\Resources\OrderResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
