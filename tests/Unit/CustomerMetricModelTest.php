<?php

namespace Tests\Unit;

use App\Models\CustomerMetric;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerMetricModelTest extends TestCase
{
    use RefreshDatabase;

    private function makeMetric(User $user, array $overrides = []): CustomerMetric
    {
        return CustomerMetric::create(array_merge([
            'user_id' => $user->id,
            'lifetime_value' => 0,
            'average_order_value' => 0,
            'total_orders' => 0,
            'total_items_purchased' => 0,
        ], $overrides));
    }

    public function test_customer_metric_can_be_created(): void
    {
        $user = User::factory()->create();
        $metric = $this->makeMetric($user, ['lifetime_value' => 500.00, 'total_orders' => 5]);

        $this->assertInstanceOf(CustomerMetric::class, $metric);
        $this->assertEquals(5, $metric->total_orders);
    }

    public function test_integer_casts(): void
    {
        $user = User::factory()->create();
        $metric = $this->makeMetric($user, ['total_orders' => 3, 'total_items_purchased' => 10]);

        $fresh = $metric->fresh();
        $this->assertIsInt($fresh->total_orders);
        $this->assertIsInt($fresh->total_items_purchased);
    }

    public function test_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $metric = $this->makeMetric($user);

        $this->assertInstanceOf(User::class, $metric->user);
        $this->assertEquals($user->id, $metric->user->id);
    }

    public function test_customer_segment_defaults_to_new_with_no_orders(): void
    {
        $user = User::factory()->create();
        $metric = $this->makeMetric($user, [
            'total_orders' => 0,
            'customer_segment' => 'new',
        ]);

        $this->assertEquals('new', $metric->customer_segment);
    }
}
