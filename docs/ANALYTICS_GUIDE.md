# Analytics Implementation Guide

## Quick Start

This guide helps you get started with the new analytics and reporting features.

## For Administrators

### Accessing Analytics

1. **Dashboard**: Login to the admin panel at `/admin`
   - View key metrics on the main dashboard
   - See Sales Overview, Inventory Stats widgets

2. **Reports Page**: Click "Reports" in the sidebar
   - Comprehensive analytics with all widgets
   - Sales trends, customer insights, inventory management

### Understanding the Metrics

#### Sales Overview Widget
- **Total Revenue**: Sum of all paid orders in the last 30 days
- **Total Orders**: Count of paid orders
- **Average Order Value**: Revenue divided by order count
- **Growth %**: Comparison with previous 30-day period

#### Inventory Stats Widget
- **Inventory Value**: Total value of products in stock (quantity × price)
- **Low Stock Items**: Products below their threshold
- **Out of Stock**: Products with zero inventory

#### Customer Segments
- **No Orders**: Registered but never purchased
- **One-time Buyer**: Made exactly one purchase
- **Regular Customer**: 2-5 orders
- **Loyal Customer**: More than 5 orders

## For Developers

### Using the Analytics Service

```php
use App\Services\AnalyticsService;

$analytics = app(AnalyticsService::class);

// Get sales metrics for custom date range
$metrics = $analytics->getSalesMetrics(
    startDate: now()->startOfMonth(),
    endDate: now()
);

// Get daily sales trends
$trends = $analytics->getSalesTrends('daily');

// Get top 20 products
$topProducts = $analytics->getTopProducts(20);

// Get customer demographics
$demographics = $analytics->getCustomerDemographics();

// Get inventory insights
$inventory = $analytics->getInventoryInsights();
```

### Creating Custom Widgets

#### Stats Widget Example
```php
namespace App\Filament\Admin\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class MyStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        return [
            Stat::make('Metric Name', '123')
                ->description('7.5% increase')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),
        ];
    }
}
```

#### Chart Widget Example
```php
namespace App\Filament\Admin\Widgets;

use Filament\Widgets\ChartWidget;

class MyChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Chart Title';

    protected function getData(): array
    {
        return [
            'datasets' => [
                [
                    'label' => 'Data Series',
                    'data' => [10, 20, 30, 40],
                ],
            ],
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr'],
        ];
    }

    protected function getType(): string
    {
        return 'line'; // or 'bar', 'pie', 'doughnut'
    }
}
```

#### Table Widget Example
```php
namespace App\Filament\Admin\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class MyTableWidget extends BaseWidget
{
    public function table(Table $table): Table
    {
        return $table
            ->heading('Table Title')
            ->query(YourModel::query())
            ->columns([
                Tables\Columns\TextColumn::make('column_name')
                    ->label('Label')
                    ->sortable(),
            ]);
    }
}
```

### Registering Widgets

Add to `app/Providers/Filament/AdminPanelProvider.php`:

```php
->widgets([
    \App\Filament\Admin\Widgets\MyCustomWidget::class,
])
```

### Adding Widgets to Pages

In your page class:

```php
protected function getHeaderWidgets(): array
{
    return [
        \App\Filament\Admin\Widgets\MyWidget::class,
    ];
}
```

## Database Requirements

### Recommended Indexes

For optimal performance, add these indexes:

```sql
-- Orders performance
CREATE INDEX idx_orders_date_status 
    ON orders(order_date, payment_status);

-- Order items performance  
CREATE INDEX idx_order_items_order_product 
    ON order_items(order_id, product_id);

-- Customer analytics
CREATE INDEX idx_customers_created 
    ON customers(created_at);

-- Product inventory
CREATE INDEX idx_products_inventory 
    ON products(inventory_count, low_stock_threshold);
```

### Required Data

Ensure your database has:
- Orders with `payment_status = 'paid'` for revenue calculations
- Customers with location data (city, state) for demographics
- Products with `inventory_count` and `low_stock_threshold` for alerts

## Customization

### Changing Date Ranges

Default is 30 days. To change:

```php
// In your widget
$analytics = app(AnalyticsService::class);
$metrics = $analytics->getSalesMetrics(
    now()->subDays(90),  // 90 days instead
    now()
);
```

### Adding Caching

For better performance:

```php
use Illuminate\Support\Facades\Cache;

$metrics = Cache::remember('sales_metrics', 300, function() {
    return app(AnalyticsService::class)->getSalesMetrics();
});
```

### Customizing Widget Appearance

```php
class MyWidget extends BaseWidget
{
    // Column span (1-12)
    protected int | string | array $columnSpan = 'full';
    
    // Sort order
    protected static ?int $sort = 1;
    
    // Height
    protected static ?string $maxHeight = '300px';
}
```

## Troubleshooting

### Widget Not Showing
1. Check it's registered in `AdminPanelProvider`
2. Clear cache: `php artisan cache:clear`
3. Check Filament cache: `php artisan filament:cache-clear`

### No Data in Charts
1. Verify you have orders with `payment_status = 'paid'`
2. Check date ranges match your data
3. Ensure relationships are properly set (customer, product)

### Slow Performance
1. Add recommended database indexes
2. Implement caching for expensive queries
3. Reduce date range if processing large datasets
4. Use `->limit()` on queries

### Permission Issues
1. Ensure user is authenticated
2. Check user has admin role
3. Verify `TeamsPermission` middleware allows access

## API Integration (Future)

If exposing analytics via API:

```php
// routes/api.php
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/analytics/sales', [AnalyticsController::class, 'sales']);
    Route::get('/analytics/customers', [AnalyticsController::class, 'customers']);
    Route::get('/analytics/inventory', [AnalyticsController::class, 'inventory']);
});
```

## Best Practices

1. **Cache Expensive Queries**: Use Laravel's cache for aggregated data
2. **Limit Data Ranges**: Don't query entire database history
3. **Use Indexes**: Add indexes to frequently queried columns
4. **Monitor Performance**: Use Laravel Telescope or similar tools
5. **Validate Inputs**: Always validate date ranges and parameters
6. **Test with Large Datasets**: Ensure queries perform well at scale

## Support

For issues or questions:
- Check documentation: `docs/ANALYTICS.md`
- Review security notes: `docs/SECURITY_REVIEW.md`
- Open GitHub issue with details

---
Last Updated: 2026-02-16
Version: 1.0.0
