<?php

namespace App\Filament\Admin\Widgets;

use App\Services\AnalyticsService;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class TopProductsWidget extends BaseWidget
{
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $analyticsService = app(AnalyticsService::class);
        $topProducts = $analyticsService->getTopProducts(10);

        return $table
            ->heading('Top 10 Products')
            ->query(
                fn () => \Illuminate\Database\Eloquent\Builder::fromRawSql(
                    'SELECT * FROM (' . collect($topProducts)->map(fn($p, $i) => 
                        "SELECT {$p->id} as id, '{$p->name}' as name, {$p->total_quantity} as total_quantity, {$p->total_revenue} as total_revenue"
                    )->implode(' UNION ALL ') . ') as t'
                )
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Product Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_quantity')
                    ->label('Units Sold')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_revenue')
                    ->label('Revenue')
                    ->money('USD')
                    ->sortable(),
            ])
            ->paginated(false);
    }
}
