<?php

namespace App\Filament\Admin\Widgets;

use App\Services\AnalyticsService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class InventoryStatsWidget extends BaseWidget
{
    protected static ?int $sort = 7;

    protected function getStats(): array
    {
        $analyticsService = app(AnalyticsService::class);
        $insights = $analyticsService->getInventoryInsights();

        return [
            Stat::make('Inventory Value', '$' . number_format($insights['inventory_value'], 2))
                ->description('Total value of current inventory')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('success'),
            
            Stat::make('Low Stock Items', number_format(count($insights['low_stock_products'])))
                ->description('Products below threshold')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('warning'),
            
            Stat::make('Out of Stock', number_format($insights['out_of_stock_count']))
                ->description('Products unavailable')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger'),
        ];
    }
}
