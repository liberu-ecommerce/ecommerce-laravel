<?php

namespace Tests\Unit;

use App\Models\CustomerSegment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerSegmentTest extends TestCase
{
    use RefreshDatabase;

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
}
