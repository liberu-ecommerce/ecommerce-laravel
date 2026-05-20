<?php

namespace App\Filament\Admin\Pages;

use Filament\Pages\Page;

class Settings extends Page
{
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected string $view = 'filament.admin.pages.settings';

    protected static ?int $navigationSort = 10;

    protected static bool $shouldRegisterNavigation = false;
}
