<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Services\EcSalesListService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EcSalesListServiceTest extends TestCase
{
    use RefreshDatabase;

    private function order(?string $vatNumber, float $value, string $status, string $date, bool $reverseCharge = true): Order
    {
        $order = Order::create([
            'customer_email' => 'buyer@example.com',
            'total_amount' => $value,
            'tax_amount' => $reverseCharge ? 0.0 : round($value * 0.2, 2),
            'status' => $status,
            'billing_country' => $vatNumber !== null ? substr($vatNumber, 0, 2) : 'DE',
            'vat_number' => $vatNumber,
            'reverse_charge' => $reverseCharge,
        ]);
        $order->forceFill(['created_at' => Carbon::parse($date)])->save();

        return $order;
    }

    private function report(): array
    {
        return app(EcSalesListService::class)->report(
            Carbon::parse('2026-04-01')->startOfDay(),
            Carbon::parse('2026-06-30')->endOfDay(),
        );
    }

    public function test_aggregates_net_supplies_per_customer_vat_number(): void
    {
        $this->order('FR12345678', 100.0, 'paid', '2026-04-10');
        $this->order('FR12345678', 250.0, 'completed', '2026-05-02');
        $this->order('IE9876543A', 400.0, 'paid', '2026-06-15');

        $report = $this->report();

        $this->assertCount(2, $report['lines']);

        $fr = collect($report['lines'])->firstWhere('vat_number', '12345678');
        $this->assertSame('FR', $fr['country']);
        $this->assertSame(2, $fr['orders']);
        $this->assertSame(350.0, $fr['value']);

        $this->assertSame(750.0, $report['totals']['value']);
        $this->assertSame(3, $report['totals']['orders']);
        $this->assertSame(2, $report['totals']['customers']);
    }

    public function test_splits_the_country_prefix_off_the_vat_number(): void
    {
        // An ESL line declares the country code and the number separately.
        $this->order('IE9876543A', 400.0, 'paid', '2026-06-15');

        $line = $this->report()['lines'][0];

        $this->assertSame('IE', $line['country']);
        $this->assertSame('9876543A', $line['vat_number']);
    }

    public function test_excludes_b2c_orders_that_charged_vat(): void
    {
        // Normal VAT-charged B2C sale — belongs on the OSS return, not the ESL.
        $this->order(null, 120.0, 'paid', '2026-04-10', reverseCharge: false);

        $this->assertSame([], $this->report()['lines']);
    }

    public function test_excludes_an_order_that_supplied_a_vat_number_but_was_still_charged_vat(): void
    {
        // A number that VIES couldn't confirm is persisted, but fails closed to normal
        // VAT — so the order is an OSS supply despite carrying a vat_number. Selecting
        // on the number alone would wrongly zero-rate it on the return.
        $this->order('FR12345678', 120.0, 'paid', '2026-04-10', reverseCharge: false);

        $this->assertSame([], $this->report()['lines']);
    }

    public function test_excludes_unpaid_and_fully_refunded_orders(): void
    {
        $this->order('FR12345678', 100.0, 'pending', '2026-04-10');
        $this->order('FR12345678', 100.0, 'failed', '2026-04-11');
        $this->order('FR12345678', 100.0, 'cancelled', '2026-04-12');
        $this->order('FR12345678', 100.0, 'refunded', '2026-04-13');

        $this->assertSame([], $this->report()['lines']);
    }

    public function test_respects_the_date_range(): void
    {
        $this->order('FR12345678', 100.0, 'paid', '2026-03-31'); // before window
        $this->order('FR12345678', 100.0, 'paid', '2026-07-01'); // after window
        $this->order('FR12345678', 100.0, 'paid', '2026-05-15'); // inside

        $report = $this->report();

        $this->assertCount(1, $report['lines']);
        $this->assertSame(100.0, $report['lines'][0]['value']);
    }

    public function test_partially_refunded_supplies_are_netted_by_the_refunded_amount(): void
    {
        // €200 supply, €50 refunded → €150 declared.
        $this->order('FR12345678', 200.0, 'partially_refunded', '2026-05-10')
            ->update(['refund_total' => 50.0]);

        $this->assertSame(150.0, $this->report()['lines'][0]['value']);
    }

    public function test_ignores_a_reverse_charge_order_with_no_vat_number(): void
    {
        // Can't be declared without a customer number — and shouldn't exist (ViesService
        // only sets reverse_charge when a number validated), so it must not silently
        // land on the return as a blank line.
        $this->order(null, 100.0, 'paid', '2026-04-10');

        $this->assertSame([], $this->report()['lines']);
    }
}
