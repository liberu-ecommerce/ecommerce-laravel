<?php

namespace App\Filament\Admin\Resources\Stores\Pages;

use App\Filament\Admin\Resources\Stores\StoreResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateStore extends CreateRecord
{
    protected static string $resource = StoreResource::class;
}
