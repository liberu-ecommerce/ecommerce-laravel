<?php

namespace App\Filament\Admin\Pages;

use Filament\Pages\Page;

class Reports extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static string $view = 'filament.admin.pages.reports';

    protected static ?int $navigationSort = 7;
}
