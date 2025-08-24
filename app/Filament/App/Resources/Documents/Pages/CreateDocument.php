<?php

namespace App\Filament\App\Resources\Documents\Pages;

use App\Filament\App\Resources\Documents\DocumentResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateDocument extends CreateRecord
{
    protected static string $resource = DocumentResource::class;
}
