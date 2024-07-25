<?php

namespace App\Filament\App\Resources\ProductTagResource\Pages;

use App\Filament\App\Resources\ProductTagResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateProductTag extends CreateRecord
{
    protected static string $resource = ProductTagResource::class;
}
