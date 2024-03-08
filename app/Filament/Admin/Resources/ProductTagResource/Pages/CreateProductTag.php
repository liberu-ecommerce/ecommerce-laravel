<?php

namespace App\Filament\Admin\Resources\ProductTagResource\Pages;

use App\Filament\Admin\Resources\ProductTagResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateProductTag extends CreateRecord
{
    protected static string $resource = ProductTagResource::class;
}
