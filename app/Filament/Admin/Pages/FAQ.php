<?php

namespace App\Filament\Admin\Pages;

use Filament\Pages\Page;

class FAQ extends Page
{
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-question-mark-circle';

    protected string $view = 'filament.admin.pages.f-a-q';

    protected static ?int $navigationSort = 5;
}
