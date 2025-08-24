<?php

namespace App\Filament\App\Resources\ContactRequestResource\Pages;

use App\Filament\App\Resources\ContactRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateContactRequest extends CreateRecord
{
    protected static string $resource = ContactRequestResource::class;
}
