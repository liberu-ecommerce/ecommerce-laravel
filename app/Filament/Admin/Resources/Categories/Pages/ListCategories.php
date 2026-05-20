<?php

namespace App\Filament\Admin\Resources\Categories\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Admin\Resources\Categories\CategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCategories extends ListRecords
{
    protected static string $resource = CategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
