<?php

namespace App\Filament\App\Resources\Groups\Pages;

use Filament\Actions\CreateAction;
use App\Filament\App\Resources\Groups\GroupResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGroups extends ListRecords
{
    protected static string $resource = GroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
