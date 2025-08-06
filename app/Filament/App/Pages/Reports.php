<?php

namespace App\Filament\App\Pages;

use Filament\Pages\Page;

class Reports extends Page
{
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-chart-pie';

    protected string $view = 'filament.app.pages.reports';

    protected static ?int $navigationSort = 9;
}
