<?php

namespace App\Filament\Admin\Pages;

use Filament\Pages\Page;

class Reports extends Page
{
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-chart-pie';

    protected string $view = 'filament.admin.pages.reports';

    protected static ?int $navigationSort = 8;

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Admin\Widgets\SalesOverviewWidget::class,
            \App\Filament\Admin\Widgets\InventoryStatsWidget::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int | array
    {
        return 2;
    }

    protected function getFooterWidgets(): array
    {
        return [
            \App\Filament\Admin\Widgets\SalesTrendsChart::class,
            \App\Filament\Admin\Widgets\CustomerDemographicsWidget::class,
            \App\Filament\Admin\Widgets\CustomerGrowthWidget::class,
            \App\Filament\Admin\Widgets\TopProductsWidget::class,
            \App\Filament\Admin\Widgets\LowStockInventoryWidget::class,
            \App\Filament\Admin\Widgets\RecentOrdersWidget::class,
        ];
    }
}
