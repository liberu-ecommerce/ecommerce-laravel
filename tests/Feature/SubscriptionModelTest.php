<?php

namespace Tests\Feature;

use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubscriptionModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_subscription_persists_to_its_table_and_relates_to_user(): void
    {
        // Regression: model previously fatal-loaded (`use Laravel\Cashier\Billable`
        // with cashier not installed) and had no `subscriptions` table migration.
        $user = User::factory()->create();

        $subscription = new Subscription([
            'name' => 'default',
            'stripe_id' => 'sub_123',
            'stripe_status' => 'active',
            'stripe_plan' => 'plan_456',
            'quantity' => 1,
        ]);
        $subscription->user()->associate($user)->save();

        $this->assertDatabaseHas('subscriptions', [
            'id' => $subscription->id,
            'user_id' => $user->id,
            'stripe_id' => 'sub_123',
        ]);

        $fresh = $subscription->fresh();
        $this->assertTrue($fresh->isActive());
        $this->assertTrue($fresh->user->is($user));
    }

    public function test_is_active_is_false_when_not_active(): void
    {
        $user = User::factory()->create();

        $subscription = new Subscription(['name' => 'default', 'stripe_status' => 'canceled']);
        $subscription->user()->associate($user)->save();

        $this->assertFalse($subscription->isActive());
    }
}
