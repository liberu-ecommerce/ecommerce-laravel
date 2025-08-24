<?php

namespace App\Filament\App\Resources\Collections\Pages;

use App\Filament\App\Resources\Collections\CollectionResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCollection extends CreateRecord
{
    protected static string $resource = CollectionResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
