<?php

namespace App\Filament\Admin\Resources\TaxClasses\Pages;

use App\Filament\Admin\Resources\TaxClasses\TaxClassResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTaxClass extends CreateRecord
{
    protected static string $resource = TaxClassResource::class;
}
