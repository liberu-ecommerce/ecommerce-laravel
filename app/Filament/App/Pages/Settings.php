<?php

namespace App\Filament\App\Pages;

use Filament\Pages\Page;

class Settings extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string $view = 'filament.app.pages.settings';

    protected static ?int $navigationSort = 12;
}
