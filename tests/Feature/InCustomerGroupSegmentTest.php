<?php

namespace Tests\Feature;

use App\Models\CustomerGroup;
use App\Models\CustomerSegment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The in_customer_group segment condition matches users by the customer group their
 * (User<->Customer identity-linked) customer belongs to. It used to query a
 * nonexistent users.customer_group_id column and error out.
 */
class InCustomerGroupSegmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_in_customer_group_matches_users_whose_customer_is_in_the_group(): void
    {
        $group = CustomerGroup::create(['name' => 'VIP', 'is_active' => true]);

        $inUser = User::factory()->create();
        $inUser->getOrCreateCustomer()->groups()->attach($group->id, ['joined_at' => now()]);

        $outUser = User::factory()->create();
        $outUser->getOrCreateCustomer(); // has a customer, but not in the group

        $segment = CustomerSegment::create([
            'name' => 'VIPs',
            'match_type' => 'all',
            'is_active' => true,
            'conditions' => [['field' => 'in_customer_group', 'operator' => '=', 'value' => $group->id]],
        ]);

        $segment->calculateMembers();

        $memberIds = $segment->members()->pluck('users.id');
        $this->assertTrue($memberIds->contains($inUser->id), 'A user whose customer is in the group must match');
        $this->assertFalse($memberIds->contains($outUser->id), 'A user not in the group must not match');
        $this->assertEquals(1, $segment->fresh()->customer_count);
    }
}
