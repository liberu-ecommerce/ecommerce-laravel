<?php

namespace App\Filament\App\Resources\ContactRequests\Pages;

use App\Filament\App\Resources\ContactRequests\ContactRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateContactRequest extends CreateRecord
{
    protected static string $resource = ContactRequestResource::class;
}
