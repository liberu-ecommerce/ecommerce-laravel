<?php

namespace Tests\Feature;

use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OssVatReportCommandTest extends TestCase
{
    use RefreshDatabase;

    private function paidGermanOrder(): void
    {
        Order::create([
            'customer_email' => 'buyer@example.com',
            'total_amount' => 119.0,
            'tax_amount' => 19.0,
            'status' => 'paid',
            'billing_country' => 'DE',
        ])->forceFill(['created_at' => Carbon::parse('2026-05-10')])->save();
    }

    public function test_reports_eu_vat_for_the_period(): void
    {
        $this->paidGermanOrder();

        $this->artisan('vat:oss-report', ['--from' => '2026-04-01', '--to' => '2026-06-30'])
            ->expectsOutputToContain('DE')
            ->assertSuccessful();
    }

    public function test_warns_when_no_eu_sales(): void
    {
        $this->artisan('vat:oss-report', ['--from' => '2026-04-01', '--to' => '2026-06-30'])
            ->expectsOutputToContain('No EU sales')
            ->assertSuccessful();
    }

    public function test_rejects_an_inverted_date_range(): void
    {
        $this->artisan('vat:oss-report', ['--from' => '2026-06-30', '--to' => '2026-04-01'])
            ->assertFailed();
    }

    public function test_csv_output(): void
    {
        $this->paidGermanOrder();

        $this->artisan('vat:oss-report', ['--from' => '2026-04-01', '--to' => '2026-06-30', '--csv' => true])
            ->expectsOutputToContain('member_state,standard_rate,orders,net,vat,gross')
            ->expectsOutputToContain('DE,19,1,100,19,119')
            ->assertSuccessful();
    }
}
