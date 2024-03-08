<?php

namespace App\Filament\Admin\Resources\ProductTagResource\Pages;

use App\Filament\Admin\Resources\ProductTagResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProductTags extends ListRecords
{
    protected static string $resource = ProductTagResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
