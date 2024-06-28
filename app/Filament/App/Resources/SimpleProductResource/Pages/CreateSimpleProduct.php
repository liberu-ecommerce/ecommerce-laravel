<?php

namespace App\Filament\Admin\Resources\SimpleProductResource\Pages;

use App\Filament\Admin\Resources\SimpleProductResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateSimpleProduct extends CreateRecord
{
    protected static string $resource = SimpleProductResource::class;
}
