<?php

namespace App\Filament\App\Pages;

use Filament\Pages\Page;

class Settings extends Page
{
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected string $view = 'filament.app.pages.settings';

    protected static ?int $navigationSort = 12;
}
