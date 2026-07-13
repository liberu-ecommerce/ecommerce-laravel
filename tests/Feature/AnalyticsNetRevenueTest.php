<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Services\AnalyticsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Refunded orders deliberately keep payment_status='paid' (the payment happened;
 * refund_total tracks the money returned — see Order::transitionTo). So revenue
 * reporting must subtract refund_total, otherwise a fully refunded order still
 * shows as full gross revenue on the admin dashboard.
 */
class AnalyticsNetRevenueTest extends TestCase
{
    use RefreshDatabase;

    private function paidOrder(float $total, float $refund, string $status = 'paid'): Order
    {
        return Order::create([
            'customer_email' => 'buyer@example.com',
            'order_date' => now(),
            'payment_status' => 'paid',
            'status' => $status,
            'total_amount' => $total,
            'refund_total' => $refund,
        ]);
    }

    public function test_sales_metrics_report_revenue_net_of_refunds(): void
    {
        $this->paidOrder(500, 0);                               // full 500
        $this->paidOrder(300, 300, 'refunded');                // fully refunded → 0
        $this->paidOrder(200, 50, 'partially_refunded');       // nets 150

        $metrics = app(AnalyticsService::class)->getSalesMetrics();

        // 500 + 0 + 150 = 650, not the gross 1000.
        $this->assertEquals(650.0, $metrics['total_revenue']);
        $this->assertEquals(3, $metrics['order_count']);
    }

    public function test_sales_trends_report_revenue_net_of_refunds(): void
    {
        $this->paidOrder(500, 0);
        $this->paidOrder(300, 300, 'refunded');

        $trends = app(AnalyticsService::class)->getSalesTrends('daily');

        // Both orders fall on the same day → one bucket, net = 500 + 0.
        $this->assertCount(1, $trends);
        $this->assertEquals(500.0, (float) $trends[0]['total_revenue']);
    }
}
