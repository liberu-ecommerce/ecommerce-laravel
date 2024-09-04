<?php

namespace App\Filament\Admin\Resources\ProductResource\Pages;

use App\Filament\Admin\Resources\ProductResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('import')
                ->label('Import Products')
                ->icon('heroicon-o-arrow-up-tray')
                ->action(fn () => $this->import()),
        ];
    }

    protected function import()
    {
        // Implement import functionality here
    }
}