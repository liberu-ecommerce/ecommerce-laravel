<?php

namespace Tests\Feature;

use App\Models\ShippingQuote;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PruneShippingQuotesTest extends TestCase
{
    use RefreshDatabase;

    private function quote(string $expiresAt): ShippingQuote
    {
        return ShippingQuote::create([
            'user_id' => User::factory()->create()->id,
            'session_id' => 'api',
            'carrier' => 'USPS', 'service' => 'Priority', 'amount' => 5, 'currency' => 'USD',
            'expires_at' => $expiresAt,
        ]);
    }

    public function test_prunes_quotes_past_the_grace_window_and_keeps_the_rest(): void
    {
        $old = $this->quote(now()->subDays(3));       // long expired
        $recent = $this->quote(now()->subHours(1));   // expired, within the 1-day grace
        $active = $this->quote(now()->addHour());     // still valid

        $this->artisan('shipping:prune-quotes')       // default --days=1
            ->expectsOutputToContain('Pruned 1 expired shipping quote(s).')
            ->assertSuccessful();

        $this->assertDatabaseMissing('shipping_quotes', ['id' => $old->id]);
        $this->assertDatabaseHas('shipping_quotes', ['id' => $recent->id]);
        $this->assertDatabaseHas('shipping_quotes', ['id' => $active->id]);
    }

    public function test_days_zero_prunes_everything_already_expired(): void
    {
        $this->quote(now()->subHours(1));
        $active = $this->quote(now()->addHour());

        $this->artisan('shipping:prune-quotes', ['--days' => 0])->assertSuccessful();

        $this->assertSame(1, ShippingQuote::count());
        $this->assertDatabaseHas('shipping_quotes', ['id' => $active->id]);
    }
}
