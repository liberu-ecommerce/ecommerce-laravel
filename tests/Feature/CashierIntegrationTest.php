<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Laravel\Cashier\Subscription as CashierSubscription;
use Tests\TestCase;

/**
 * Cashier is now installed and User is Billable. This locks the wiring in place:
 * the billable has Cashier's Stripe columns + methods, and the hand-rolled
 * subscriptions table was renamed to Cashier v16's schema (type / stripe_price)
 * with a subscription_items table. (End-to-end subscription creation hits the
 * Stripe API and needs test credentials, so it is not exercised here.)
 */
class CashierIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_is_billable(): void
    {
        $user = User::factory()->create();

        $this->assertTrue(method_exists($user, 'newSubscription'));
        $this->assertTrue(method_exists($user, 'subscription'));
        $this->assertInstanceOf(HasMany::class, $user->subscriptions());
        $this->assertSame(CashierSubscription::class, $user->subscriptions()->getRelated()::class);
    }

    public function test_users_table_has_the_cashier_stripe_columns(): void
    {
        foreach (['stripe_id', 'pm_type', 'pm_last_four', 'trial_ends_at'] as $column) {
            $this->assertTrue(Schema::hasColumn('users', $column), "users.$column missing");
        }
    }

    public function test_subscriptions_table_matches_cashier_v16_schema(): void
    {
        $this->assertTrue(Schema::hasColumn('subscriptions', 'type'));
        $this->assertTrue(Schema::hasColumn('subscriptions', 'stripe_price'));
        $this->assertFalse(Schema::hasColumn('subscriptions', 'name'));
        $this->assertFalse(Schema::hasColumn('subscriptions', 'stripe_plan'));
        $this->assertTrue(Schema::hasTable('subscription_items'));
    }
}
