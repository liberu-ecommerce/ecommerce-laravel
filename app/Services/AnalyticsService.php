<?php

namespace App\Services;

use App\Models\AnalyticsEvent;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\InventoryItem;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AnalyticsService
{
    /**
     * Get sales trends for a given period
     */
    public function getSalesTrends(string $period = 'daily', ?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $startDate = $startDate ?? now()->subDays(30);
        $endDate = $endDate ?? now();

        $groupBy = match($period) {
            'hourly' => "DATE_FORMAT(order_date, '%Y-%m-%d %H:00:00')",
            'daily' => "DATE(order_date)",
            'weekly' => "YEARWEEK(order_date, 1)",
            'monthly' => "DATE_FORMAT(order_date, '%Y-%m')",
            default => "DATE(order_date)"
        };

        $sales = Order::whereBetween('order_date', [$startDate, $endDate])
            ->where('payment_status', 'paid')
            ->select(
                DB::raw("{$groupBy} as period"),
                DB::raw('COUNT(*) as order_count'),
                DB::raw('SUM(total_amount) as total_revenue'),
                DB::raw('AVG(total_amount) as avg_order_value')
            )
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        return $sales->toArray();
    }

    /**
     * Get overall sales metrics
     */
    public function getSalesMetrics(?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $startDate = $startDate ?? now()->subDays(30);
        $endDate = $endDate ?? now();

        $orders = Order::whereBetween('order_date', [$startDate, $endDate])
            ->where('payment_status', 'paid');

        $totalRevenue = $orders->sum('total_amount');
        $orderCount = $orders->count();
        $avgOrderValue = $orderCount > 0 ? $totalRevenue / $orderCount : 0;

        // Compare with previous period
        $previousPeriod = $startDate->diffInDays($endDate);
        $previousStartDate = $startDate->copy()->subDays($previousPeriod);
        $previousEndDate = $startDate->copy()->subDay();

        $previousOrders = Order::whereBetween('order_date', [$previousStartDate, $previousEndDate])
            ->where('payment_status', 'paid');
        
        $previousRevenue = $previousOrders->sum('total_amount');
        $previousOrderCount = $previousOrders->count();

        $revenueGrowth = $previousRevenue > 0 
            ? (($totalRevenue - $previousRevenue) / $previousRevenue) * 100 
            : 0;
        
        $orderGrowth = $previousOrderCount > 0 
            ? (($orderCount - $previousOrderCount) / $previousOrderCount) * 100 
            : 0;

        return [
            'total_revenue' => round($totalRevenue, 2),
            'order_count' => $orderCount,
            'avg_order_value' => round($avgOrderValue, 2),
            'revenue_growth' => round($revenueGrowth, 2),
            'order_growth' => round($orderGrowth, 2),
        ];
    }

    /**
     * Get top selling products
     */
    public function getTopProducts(int $limit = 10, ?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $startDate = $startDate ?? now()->subDays(30);
        $endDate = $endDate ?? now();

        $topProducts = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->whereBetween('orders.order_date', [$startDate, $endDate])
            ->where('orders.payment_status', 'paid')
            ->select(
                'products.id',
                'products.name',
                DB::raw('SUM(order_items.quantity) as total_quantity'),
                DB::raw('SUM(order_items.price * order_items.quantity) as total_revenue')
            )
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_revenue')
            ->limit($limit)
            ->get();

        return $topProducts->toArray();
    }

    /**
     * Get customer demographics
     */
    public function getCustomerDemographics(): array
    {
        $totalCustomers = Customer::count();
        
        // Customers by location (city)
        $byCity = Customer::select('city', DB::raw('COUNT(*) as count'))
            ->whereNotNull('city')
            ->groupBy('city')
            ->orderByDesc('count')
            ->limit(10)
            ->get()
            ->toArray();

        // Customers by state
        $byState = Customer::select('state', DB::raw('COUNT(*) as count'))
            ->whereNotNull('state')
            ->groupBy('state')
            ->orderByDesc('count')
            ->limit(10)
            ->get()
            ->toArray();

        // Customer segmentation by order count
        $customerSegments = DB::table('customers')
            ->leftJoin('orders', 'customers.id', '=', 'orders.customer_id')
            ->select(
                DB::raw("CASE 
                    WHEN COUNT(orders.id) = 0 THEN 'No Orders'
                    WHEN COUNT(orders.id) = 1 THEN 'One-time Buyer'
                    WHEN COUNT(orders.id) BETWEEN 2 AND 5 THEN 'Regular Customer'
                    WHEN COUNT(orders.id) > 5 THEN 'Loyal Customer'
                END as segment"),
                DB::raw('COUNT(DISTINCT customers.id) as customer_count')
            )
            ->groupBy('segment')
            ->get()
            ->toArray();

        // Top customers by revenue
        $topCustomers = Customer::select(
                'customers.id',
                'customers.first_name',
                'customers.last_name',
                'customers.email',
                DB::raw('COUNT(orders.id) as order_count'),
                DB::raw('SUM(orders.total_amount) as lifetime_value')
            )
            ->join('orders', 'customers.id', '=', 'orders.customer_id')
            ->where('orders.payment_status', 'paid')
            ->groupBy('customers.id', 'customers.first_name', 'customers.last_name', 'customers.email')
            ->orderByDesc('lifetime_value')
            ->limit(10)
            ->get()
            ->toArray();

        return [
            'total_customers' => $totalCustomers,
            'by_city' => $byCity,
            'by_state' => $byState,
            'segments' => $customerSegments,
            'top_customers' => $topCustomers,
        ];
    }

    /**
     * Get inventory insights
     */
    public function getInventoryInsights(): array
    {
        // Low stock products
        $lowStockProducts = Product::whereNotNull('low_stock_threshold')
            ->whereColumn('inventory_count', '<=', 'low_stock_threshold')
            ->select('id', 'name', 'inventory_count', 'low_stock_threshold')
            ->orderBy('inventory_count')
            ->limit(20)
            ->get()
            ->toArray();

        // Out of stock products
        $outOfStockCount = Product::where('inventory_count', '<=', 0)->count();

        // Inventory value
        $inventoryValue = Product::where('inventory_count', '>', 0)
            ->select(DB::raw('SUM(inventory_count * price) as total_value'))
            ->value('total_value') ?? 0;

        // Products by stock status
        $stockStatus = [
            'out_of_stock' => Product::where('inventory_count', '<=', 0)->count(),
            'low_stock' => Product::whereNotNull('low_stock_threshold')
                ->whereColumn('inventory_count', '<=', 'low_stock_threshold')
                ->where('inventory_count', '>', 0)
                ->count(),
            'in_stock' => Product::where('inventory_count', '>', 0)
                ->where(function($q) {
                    $q->whereNull('low_stock_threshold')
                        ->orWhereColumn('inventory_count', '>', 'low_stock_threshold');
                })
                ->count(),
        ];

        return [
            'low_stock_products' => $lowStockProducts,
            'out_of_stock_count' => $outOfStockCount,
            'inventory_value' => round($inventoryValue, 2),
            'stock_status' => $stockStatus,
        ];
    }

    /**
     * Get recent orders
     */
    public function getRecentOrders(int $limit = 10): array
    {
        return Order::with('customer:id,first_name,last_name,email')
            ->orderByDesc('order_date')
            ->limit($limit)
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'order_date' => $order->order_date,
                    'customer_name' => $order->customer 
                        ? $order->customer->first_name . ' ' . $order->customer->last_name 
                        : 'Guest',
                    'total_amount' => $order->total_amount,
                    'payment_status' => $order->payment_status,
                    'shipping_status' => $order->shipping_status,
                ];
            })
            ->toArray();
    }
}
