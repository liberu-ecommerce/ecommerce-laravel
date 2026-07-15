<?php

namespace Tests\Feature;

use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EcSalesListCommandTest extends TestCase
{
    use RefreshDatabase;

    private function reverseChargedFrenchOrder(): void
    {
        Order::create([
            'customer_email' => 'buyer@example.com',
            'total_amount' => 100.0,
            'tax_amount' => 0.0,
            'status' => 'paid',
            'billing_country' => 'FR',
            'vat_number' => 'FR12345678',
            'reverse_charge' => true,
        ])->forceFill(['created_at' => Carbon::parse('2026-05-10')])->save();
    }

    public function test_reports_b2b_supplies_for_the_period(): void
    {
        $this->reverseChargedFrenchOrder();

        $this->artisan('vat:ec-sales-list', ['--from' => '2026-04-01', '--to' => '2026-06-30'])
            ->expectsOutputToContain('12345678')
            ->assertSuccessful();
    }

    public function test_warns_when_no_b2b_supplies(): void
    {
        $this->artisan('vat:ec-sales-list', ['--from' => '2026-04-01', '--to' => '2026-06-30'])
            ->expectsOutputToContain('No reverse-charge B2B supplies')
            ->assertSuccessful();
    }

    public function test_rejects_an_inverted_date_range(): void
    {
        $this->artisan('vat:ec-sales-list', ['--from' => '2026-06-30', '--to' => '2026-04-01'])
            ->assertFailed();
    }

    public function test_csv_output(): void
    {
        $this->reverseChargedFrenchOrder();

        $this->artisan('vat:ec-sales-list', ['--from' => '2026-04-01', '--to' => '2026-06-30', '--csv' => true])
            ->expectsOutputToContain('country,vat_number,orders,value')
            ->expectsOutputToContain('FR,12345678,1,100')
            ->assertSuccessful();
    }
}
