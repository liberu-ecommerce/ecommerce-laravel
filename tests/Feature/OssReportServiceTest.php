<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Services\OssReportService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OssReportServiceTest extends TestCase
{
    use RefreshDatabase;

    private function order(string $country, float $gross, float $vat, string $status, string $date): Order
    {
        $order = Order::create([
            'customer_email' => 'buyer@example.com',
            'total_amount' => $gross,
            'tax_amount' => $vat,
            'status' => $status,
            'billing_country' => $country,
        ]);
        $order->forceFill(['created_at' => Carbon::parse($date)])->save();

        return $order;
    }

    private function report(): array
    {
        return app(OssReportService::class)->report(
            Carbon::parse('2026-04-01')->startOfDay(),
            Carbon::parse('2026-06-30')->endOfDay(),
        );
    }

    public function test_aggregates_vat_per_member_state(): void
    {
        $this->order('DE', 119.0, 19.0, 'paid', '2026-04-10');
        $this->order('DE', 119.0, 19.0, 'completed', '2026-05-02');
        $this->order('FR', 120.0, 20.0, 'paid', '2026-06-15');

        $report = $this->report();

        $this->assertCount(2, $report['lines']);
        $de = collect($report['lines'])->firstWhere('country', 'DE');
        $this->assertSame(2, $de['orders']);
        $this->assertSame(38.0, $de['vat']);
        $this->assertSame(200.0, $de['net']);   // (119-19) * 2
        $this->assertSame(238.0, $de['gross']);
        $this->assertSame(19.0, $de['standard_rate']);

        $this->assertSame(58.0, $report['totals']['vat']);   // 38 + 20
        $this->assertSame(3, $report['totals']['orders']);
    }

    public function test_excludes_non_eu_countries(): void
    {
        $this->order('US', 110.0, 10.0, 'paid', '2026-04-10');
        $this->order('GB', 120.0, 20.0, 'paid', '2026-04-11'); // post-Brexit, not EU

        $this->assertSame([], $this->report()['lines']);
    }

    public function test_excludes_unpaid_and_fully_refunded_orders(): void
    {
        $this->order('DE', 119.0, 19.0, 'pending', '2026-04-10');
        $this->order('DE', 119.0, 19.0, 'failed', '2026-04-11');
        $this->order('DE', 119.0, 19.0, 'cancelled', '2026-04-12');
        $this->order('DE', 119.0, 19.0, 'refunded', '2026-04-13');

        $this->assertSame([], $this->report()['lines']);
    }

    public function test_respects_the_date_range(): void
    {
        $this->order('DE', 119.0, 19.0, 'paid', '2026-03-31'); // before window
        $this->order('DE', 119.0, 19.0, 'paid', '2026-07-01'); // after window
        $this->order('DE', 119.0, 19.0, 'paid', '2026-05-15'); // inside

        $report = $this->report();
        $this->assertCount(1, $report['lines']);
        $this->assertSame(1, $report['lines'][0]['orders']);
    }
}
