<?php

namespace App\Filament\App\Resources\SimpleProductResource\Pages;

use App\Filament\App\Resources\SimpleProductResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateSimpleProduct extends CreateRecord
{
    protected static string $resource = SimpleProductResource::class;
}
