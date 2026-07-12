<?php

namespace Tests\Unit;

use App\Models\Customer;
use App\Models\CustomerMetric;
use App\Models\CustomerSegment;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerSegmentTest extends TestCase
{
    use RefreshDatabase;

    private function makeSegment(array $conditions, string $matchType): CustomerSegment
    {
        return CustomerSegment::create([
            'name' => 'seg',
            'conditions' => $conditions,
            'match_type' => $matchType,
            'is_active' => true,
        ]);
    }

    private function userWithLtv(float $ltv): User
    {
        $user = User::factory()->create();
        CustomerMetric::create([
            'user_id' => $user->id,
            'lifetime_value' => $ltv,
            'total_orders' => 0,
        ]);

        return $user;
    }

    private function userWithOrderCount(int $count): User
    {
        $user = User::factory()->create();
        $customer = Customer::create([
            'first_name' => 'A', 'last_name' => 'B', 'email' => uniqid().'@x.com',
            'phone_number' => 5551234, 'address' => 'a', 'city' => 'c', 'state' => 's', 'postal_code' => 'z',
        ]);

        for ($i = 0; $i < $count; $i++) {
            Order::create([
                'user_id' => $user->id,
                'customer_id' => $customer->id,
                'order_date' => now()->toDateString(),
                'total_amount' => 100,
                'payment_status' => 'paid',
                'shipping_status' => 'pending',
                'status' => 'paid',
            ]);
        }

        return $user;
    }

    public function test_customer_segment_can_be_created()
    {
        $segment = CustomerSegment::factory()->create([
            'name' => 'VIP Customers',
            'conditions' => [
                [
                    'field' => 'lifetime_value',
                    'operator' => '>=',
                    'value' => 1000
                ]
            ],
            'match_type' => 'all',
        ]);

        $this->assertDatabaseHas('customer_segments', [
            'name' => 'VIP Customers',
            'match_type' => 'all',
        ]);
    }

    public function test_segment_can_have_members()
    {
        $segment = CustomerSegment::factory()->create();
        $user = User::factory()->create();

        $segment->members()->attach($user->id);

        $this->assertTrue($segment->members->contains($user));
    }

    public function test_active_scope_filters_active_segments()
    {
        CustomerSegment::factory()->create(['is_active' => true]);
        CustomerSegment::factory()->create(['is_active' => false]);

        $activeSegments = CustomerSegment::active()->get();

        $this->assertEquals(1, $activeSegments->count());
    }

    public function test_match_type_any_matches_users_satisfying_either_condition(): void
    {
        $rich = $this->userWithLtv(2000);
        $poor = $this->userWithLtv(5);
        $mid  = $this->userWithLtv(500);

        $segment = $this->makeSegment([
            ['field' => 'lifetime_value', 'operator' => '>=', 'value' => 1000],
            ['field' => 'lifetime_value', 'operator' => '<=', 'value' => 10],
        ], 'any');

        $segment->calculateMembers();

        $ids = $segment->members()->pluck('users.id')->all();
        $this->assertContains($rich->id, $ids, 'lifetime_value >= 1000 should match under "any"');
        $this->assertContains($poor->id, $ids, 'lifetime_value <= 10 should match under "any"');
        $this->assertNotContains($mid->id, $ids, 'user matching neither condition must be excluded');
        $this->assertEquals(2, $segment->customer_count);
    }

    public function test_match_type_all_requires_every_condition(): void
    {
        $mid  = $this->userWithLtv(500);
        $rich = $this->userWithLtv(2000);
        $poor = $this->userWithLtv(5);

        $segment = $this->makeSegment([
            ['field' => 'lifetime_value', 'operator' => '>=', 'value' => 100],
            ['field' => 'lifetime_value', 'operator' => '<=', 'value' => 1000],
        ], 'all');

        $segment->calculateMembers();

        $ids = $segment->members()->pluck('users.id')->all();
        $this->assertContains($mid->id, $ids, 'user in [100,1000] should match under "all"');
        $this->assertNotContains($rich->id, $ids, 'user above upper bound must be excluded');
        $this->assertNotContains($poor->id, $ids, 'user below lower bound must be excluded');
    }

    public function test_total_orders_condition_matches_at_boundary_without_fataling(): void
    {
        $twoOrders = $this->userWithOrderCount(2);
        $oneOrder  = $this->userWithOrderCount(1);

        $matched = function (string $operator, int $value): array {
            $segment = $this->makeSegment(
                [['field' => 'total_orders', 'operator' => $operator, 'value' => $value]],
                'all'
            );
            $segment->calculateMembers();

            return $segment->members()->pluck('users.id')->all();
        };

        // Boundary: user with exactly 2 orders
        $this->assertContains($twoOrders->id, $matched('>=', 2));
        $this->assertNotContains($twoOrders->id, $matched('>=', 3));
        $this->assertContains($twoOrders->id, $matched('=', 2));
        $this->assertContains($twoOrders->id, $matched('>', 1));
        $this->assertNotContains($twoOrders->id, $matched('>', 2));

        // The single-order user must never match a >= 2 threshold
        $this->assertNotContains($oneOrder->id, $matched('>=', 2));
    }
}
