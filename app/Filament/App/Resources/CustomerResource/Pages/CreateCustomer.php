<?php

namespace App\Filament\App\Resources\CustomerResource\Pages;

use App\Filament\App\Resources\CustomerResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCustomer extends CreateRecord
{
    protected static string $resource = CustomerResource::class;
}
