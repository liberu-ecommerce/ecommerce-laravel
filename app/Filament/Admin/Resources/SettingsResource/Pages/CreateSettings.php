<?php

namespace App\Filament\Admin\Resources\SettingsResource\Pages;

use App\Filament\Admin\Resources\SettingsResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateSettings extends CreateRecord
{
    protected static string $resource = SettingsResource::class;
}
