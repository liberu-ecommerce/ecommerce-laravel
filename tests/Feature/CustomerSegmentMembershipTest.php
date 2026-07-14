<?php

namespace Tests\Feature;

use App\Models\CustomerSegment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The customerSegments() pivot has its own `added_at` and no created_at/updated_at, so
 * the relation must not declare withTimestamps() — otherwise attaching or reading a
 * membership errors on the missing pivot timestamp columns.
 */
class CustomerSegmentMembershipTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_user_can_be_attached_to_and_read_from_a_segment(): void
    {
        $user = User::factory()->create();
        $segment = CustomerSegment::create(['name' => 'VIP', 'match_type' => 'all', 'conditions' => []]);

        $user->customerSegments()->attach($segment->id);

        $this->assertTrue($user->customerSegments()->where('customer_segments.id', $segment->id)->exists());
        $this->assertNotNull($user->customerSegments()->first()->pivot->added_at);
    }
}
