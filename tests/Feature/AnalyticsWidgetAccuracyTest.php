<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Services\AnalyticsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnalyticsWidgetAccuracyTest extends TestCase
{
    use RefreshDatabase;

    public function test_low_stock_count_is_not_capped_at_the_display_list_limit(): void
    {
        Product::factory()->count(25)->create(['inventory_count' => 1, 'low_stock_threshold' => 5]);

        $insights = app(AnalyticsService::class)->getInventoryInsights();

        // The display list is capped at 20, but the headline count must be the real total.
        $this->assertCount(20, $insights['low_stock_products']);
        $this->assertEquals(25, $insights['low_stock_count']);
    }

    public function test_customer_segmentation_counts_only_paid_orders(): void
    {
        $customer = Customer::create([
            'first_name' => 'A', 'last_name' => 'B', 'email' => 'seg@example.com',
            'phone_number' => 1, 'address' => 'x', 'city' => 'x', 'state' => 'x', 'postal_code' => '1',
        ]);
        // One paid order + two non-paid; only the paid one should count → "One-time Buyer".
        Order::create(['customer_id' => $customer->id, 'total_amount' => 10, 'status' => 'paid', 'payment_status' => 'paid']);
        Order::create(['customer_id' => $customer->id, 'total_amount' => 10, 'status' => 'failed', 'payment_status' => 'failed']);
        Order::create(['customer_id' => $customer->id, 'total_amount' => 10, 'status' => 'pending', 'payment_status' => 'pending']);

        $segments = collect(app(AnalyticsService::class)->getCustomerDemographics()['segments'])
            ->keyBy('segment');

        $this->assertEquals(1, $segments['One-time Buyer']->customer_count ?? 0);
        $this->assertArrayNotHasKey('Regular Customer', $segments->all());
    }
}
