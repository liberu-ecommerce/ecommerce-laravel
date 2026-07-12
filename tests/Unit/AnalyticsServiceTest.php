<?php

namespace Tests\Unit;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Services\AnalyticsService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnalyticsServiceTest extends TestCase
{
    use RefreshDatabase;

    private AnalyticsService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new AnalyticsService();
    }

    private function makeCustomer(int $index = 1): Customer
    {
        return Customer::create([
            'first_name' => "Customer$index",
            'last_name' => "Test$index",
            'email' => "customer$index@test.com",
            'phone_number' => '555-' . str_pad($index, 4, '0', STR_PAD_LEFT),
            'address' => "$index Main St",
            'city' => 'Testville',
            'state' => 'CA',
            'postal_code' => '90210',
        ]);
    }

    private function makeOrder(Customer $customer, array $overrides = []): Order
    {
        return Order::create(array_merge([
            'customer_id' => $customer->id,
            'customer_email' => $customer->email,
            'order_date' => now()->toDateString(),
            'total_amount' => 100.00,
            'payment_status' => 'paid',
            'shipping_status' => 'shipped',
            'status' => 'completed',
        ], $overrides));
    }

    private function makeProduct(array $overrides = []): Product
    {
        $category = ProductCategory::create([
            'name' => 'Analytics Cat',
            'slug' => 'analytics-cat-' . uniqid(),
        ]);

        return Product::create(array_merge([
            'name' => 'Analytics Product',
            'slug' => 'analytics-prod-' . uniqid(),
            'price' => 10.00,
            'category_id' => $category->id,
            'inventory_count' => 10,
        ], $overrides));
    }

    public function test_get_sales_metrics_returns_required_keys(): void
    {
        $metrics = $this->service->getSalesMetrics();

        $this->assertArrayHasKey('total_revenue', $metrics);
        $this->assertArrayHasKey('order_count', $metrics);
        $this->assertArrayHasKey('avg_order_value', $metrics);
        $this->assertArrayHasKey('revenue_growth', $metrics);
        $this->assertArrayHasKey('order_growth', $metrics);
    }

    public function test_get_sales_metrics_returns_zero_when_no_orders(): void
    {
        $metrics = $this->service->getSalesMetrics();

        $this->assertEquals(0, $metrics['total_revenue']);
        $this->assertEquals(0, $metrics['order_count']);
    }

    public function test_get_sales_metrics_counts_paid_orders(): void
    {
        $customer = $this->makeCustomer(1);
        $this->makeOrder($customer, ['total_amount' => 150.00, 'payment_status' => 'paid']);
        $this->makeOrder($customer, ['total_amount' => 50.00, 'payment_status' => 'pending']);

        $metrics = $this->service->getSalesMetrics();

        $this->assertEquals(1, $metrics['order_count']);
        $this->assertEquals(150.00, $metrics['total_revenue']);
    }

    public function test_get_recent_orders_returns_array(): void
    {
        $customer = $this->makeCustomer(2);
        $this->makeOrder($customer);

        $orders = $this->service->getRecentOrders(10);

        $this->assertIsArray($orders);
        $this->assertCount(1, $orders);
    }

    public function test_get_recent_orders_respects_limit(): void
    {
        $customer = $this->makeCustomer(3);
        for ($i = 0; $i < 5; $i++) {
            $this->makeOrder($customer);
        }

        $orders = $this->service->getRecentOrders(3);

        $this->assertCount(3, $orders);
    }

    public function test_get_recent_orders_includes_required_fields(): void
    {
        $customer = $this->makeCustomer(4);
        $this->makeOrder($customer);

        $orders = $this->service->getRecentOrders(1);

        $this->assertArrayHasKey('id', $orders[0]);
        $this->assertArrayHasKey('order_date', $orders[0]);
        $this->assertArrayHasKey('total_amount', $orders[0]);
        $this->assertArrayHasKey('payment_status', $orders[0]);
    }

    public function test_get_inventory_insights_returns_required_keys(): void
    {
        $insights = $this->service->getInventoryInsights();

        $this->assertArrayHasKey('low_stock_products', $insights);
        $this->assertArrayHasKey('out_of_stock_count', $insights);
        $this->assertArrayHasKey('inventory_value', $insights);
        $this->assertArrayHasKey('stock_status', $insights);
    }

    public function test_get_inventory_insights_counts_out_of_stock(): void
    {
        $this->makeProduct(['inventory_count' => 0]);
        $this->makeProduct(['inventory_count' => 5]);

        $insights = $this->service->getInventoryInsights();

        $this->assertEquals(1, $insights['out_of_stock_count']);
        $this->assertEquals(1, $insights['stock_status']['in_stock']);
    }

    public function test_get_inventory_insights_calculates_value(): void
    {
        $this->makeProduct(['price' => 10.00, 'inventory_count' => 5]);

        $insights = $this->service->getInventoryInsights();

        $this->assertEquals(50.00, $insights['inventory_value']);
    }

    public function test_get_customer_demographics_returns_required_keys(): void
    {
        $demographics = $this->service->getCustomerDemographics();

        $this->assertArrayHasKey('total_customers', $demographics);
        $this->assertArrayHasKey('by_city', $demographics);
        $this->assertArrayHasKey('by_state', $demographics);
        $this->assertArrayHasKey('segments', $demographics);
        $this->assertArrayHasKey('top_customers', $demographics);
    }

    public function test_get_customer_demographics_counts_customers(): void
    {
        $this->makeCustomer(10);
        $this->makeCustomer(11);

        $demographics = $this->service->getCustomerDemographics();

        $this->assertEquals(2, $demographics['total_customers']);
    }

    public function test_get_sales_metrics_computes_aov_and_growth(): void
    {
        Carbon::setTestNow('2026-07-12 12:00:00');
        $customer = $this->makeCustomer(20);

        $start = Carbon::parse('2026-07-03');
        $end = Carbon::parse('2026-07-12 12:00:00');

        // Current window: two paid orders -> revenue 300, count 2, AOV 150.
        $this->makeOrder($customer, ['order_date' => '2026-07-05', 'total_amount' => 200]);
        $this->makeOrder($customer, ['order_date' => '2026-07-06', 'total_amount' => 100]);
        // Previous window (before $start): one paid order -> revenue 150.
        $this->makeOrder($customer, ['order_date' => '2026-06-27', 'total_amount' => 150]);

        $metrics = $this->service->getSalesMetrics($start, $end);

        $this->assertEquals(2, $metrics['order_count']);
        $this->assertEquals(300, $metrics['total_revenue']);
        $this->assertEquals(150, $metrics['avg_order_value']);
        $this->assertEquals(100.0, $metrics['revenue_growth']); // (300-150)/150*100
        $this->assertEquals(100.0, $metrics['order_growth']);   // (2-1)/1*100
    }

    public function test_get_sales_metrics_excludes_orders_outside_range(): void
    {
        Carbon::setTestNow('2026-07-12 12:00:00');
        $customer = $this->makeCustomer(21);

        $this->makeOrder($customer, ['order_date' => '2026-07-10', 'total_amount' => 80]);
        // Way outside the default 30-day window.
        $this->makeOrder($customer, ['order_date' => '2026-01-01', 'total_amount' => 999]);

        $metrics = $this->service->getSalesMetrics();

        $this->assertEquals(1, $metrics['order_count']);
        $this->assertEquals(80, $metrics['total_revenue']);
    }

    public function test_get_sales_trends_daily_groups_and_aggregates(): void
    {
        Carbon::setTestNow('2026-07-12 12:00:00');
        $customer = $this->makeCustomer(22);

        $this->makeOrder($customer, ['order_date' => '2026-07-10', 'total_amount' => 100]);
        $this->makeOrder($customer, ['order_date' => '2026-07-10', 'total_amount' => 300]);
        $this->makeOrder($customer, ['order_date' => '2026-07-11', 'total_amount' => 50]);
        // Unpaid must be ignored.
        $this->makeOrder($customer, ['order_date' => '2026-07-10', 'total_amount' => 500, 'payment_status' => 'pending']);

        $trends = $this->service->getSalesTrends('daily');

        $this->assertCount(2, $trends);
        $byPeriod = collect($trends)->keyBy('period');
        $this->assertEquals(2, $byPeriod['2026-07-10']['order_count']);
        $this->assertEquals(400, $byPeriod['2026-07-10']['total_revenue']);
        $this->assertEquals(200, $byPeriod['2026-07-10']['avg_order_value']);
        $this->assertEquals(50, $byPeriod['2026-07-11']['total_revenue']);
    }

    public function test_get_top_products_ranks_by_revenue_and_ignores_unpaid(): void
    {
        Carbon::setTestNow('2026-07-12 12:00:00');
        $customer = $this->makeCustomer(23);
        $cheap = $this->makeProduct(['name' => 'Cheap']);
        $pricey = $this->makeProduct(['name' => 'Pricey']);

        $paid = $this->makeOrder($customer, ['order_date' => '2026-07-10', 'total_amount' => 100]);
        OrderItem::create(['order_id' => $paid->id, 'product_id' => $cheap->id, 'quantity' => 10, 'price' => 1.00]);   // rev 10
        OrderItem::create(['order_id' => $paid->id, 'product_id' => $pricey->id, 'quantity' => 2, 'price' => 50.00]);  // rev 100

        $unpaid = $this->makeOrder($customer, ['order_date' => '2026-07-10', 'total_amount' => 999, 'payment_status' => 'pending']);
        OrderItem::create(['order_id' => $unpaid->id, 'product_id' => $cheap->id, 'quantity' => 1000, 'price' => 1.00]);

        $top = json_decode(json_encode($this->service->getTopProducts(10)), true);

        $this->assertCount(2, $top);
        $this->assertEquals('Pricey', $top[0]['name']);
        $this->assertEquals(100, $top[0]['total_revenue']);
        $this->assertEquals('Cheap', $top[1]['name']);
        $this->assertEquals(10, $top[1]['total_revenue']);   // unpaid 1000 units excluded
        $this->assertEquals(10, $top[1]['total_quantity']);
    }
}
