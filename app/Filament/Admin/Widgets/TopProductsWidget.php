<?php

namespace App\Filament\Admin\Widgets;

use App\Services\AnalyticsService;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class TopProductsWidget extends BaseWidget
{
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Top 10 Products')
            ->query(
                fn () => DB::table('order_items')
                    ->join('orders', 'order_items.order_id', '=', 'orders.id')
                    ->join('products', 'order_items.product_id', '=', 'products.id')
                    ->whereBetween('orders.order_date', [now()->subDays(30), now()])
                    ->where('orders.payment_status', 'paid')
                    ->select(
                        'products.id',
                        'products.name',
                        DB::raw('SUM(order_items.quantity) as total_quantity'),
                        DB::raw('SUM(order_items.price * order_items.quantity) as total_revenue')
                    )
                    ->groupBy('products.id', 'products.name')
                    ->orderByDesc('total_revenue')
                    ->limit(10)
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
