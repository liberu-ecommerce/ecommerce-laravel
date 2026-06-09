<?php

namespace Tests\Unit;

use App\Models\CustomerSegment;
use App\Models\User;
use App\Services\CustomerSegmentationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerSegmentationServiceTest extends TestCase
{
    use RefreshDatabase;

    private CustomerSegmentationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new CustomerSegmentationService();
    }

    private function makeSegment(array $overrides = []): CustomerSegment
    {
        return CustomerSegment::create(array_merge([
            'name' => 'VIP Customers',
            'conditions' => [],
            'match_type' => 'all',
            'is_active' => true,
        ], $overrides));
    }

    public function test_is_user_in_segment_returns_false_for_non_member(): void
    {
        $user = User::factory()->create();
        $segment = $this->makeSegment();

        $result = $this->service->isUserInSegment($user->id, $segment->id);

        $this->assertFalse($result);
    }

    public function test_is_user_in_segment_returns_true_when_member(): void
    {
        $user = User::factory()->create();
        $segment = $this->makeSegment();
        $segment->members()->attach($user->id);

        $result = $this->service->isUserInSegment($user->id, $segment->id);

        $this->assertTrue($result);
    }

    public function test_is_user_in_segment_returns_false_for_non_existent_segment(): void
    {
        $user = User::factory()->create();

        $result = $this->service->isUserInSegment($user->id, 99999);

        $this->assertFalse($result);
    }

    public function test_get_user_segments_returns_segments_user_belongs_to(): void
    {
        $user = User::factory()->create();
        $segment1 = $this->makeSegment(['name' => 'Segment A']);
        $segment2 = $this->makeSegment(['name' => 'Segment B']);
        $segment3 = $this->makeSegment(['name' => 'Segment C']);

        $segment1->members()->attach($user->id);
        $segment3->members()->attach($user->id);

        $segments = $this->service->getUserSegments($user->id);

        $this->assertCount(2, $segments);
        $this->assertTrue($segments->contains('id', $segment1->id));
        $this->assertTrue($segments->contains('id', $segment3->id));
        $this->assertFalse($segments->contains('id', $segment2->id));
    }

    public function test_get_user_segments_returns_empty_when_no_memberships(): void
    {
        $user = User::factory()->create();

        $segments = $this->service->getUserSegments($user->id);

        $this->assertEmpty($segments);
    }

    public function test_get_users_for_segments_returns_segment_members(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $user3 = User::factory()->create();
        $segment = $this->makeSegment();

        $segment->members()->attach([$user1->id, $user2->id]);

        $users = $this->service->getUsersForSegments([$segment->id]);

        $this->assertCount(2, $users);
        $this->assertTrue($users->contains('id', $user1->id));
        $this->assertTrue($users->contains('id', $user2->id));
        $this->assertFalse($users->contains('id', $user3->id));
    }

    public function test_create_segment_creates_and_returns_segment(): void
    {
        $segment = $this->service->createSegment(
            'New Segment',
            ['order_count' => ['operator' => '>=', 'value' => 5]],
            'all'
        );

        $this->assertInstanceOf(CustomerSegment::class, $segment);
        $this->assertEquals('New Segment', $segment->name);
        $this->assertTrue($segment->is_active);
        $this->assertDatabaseHas('customer_segments', ['name' => 'New Segment']);
    }

    public function test_get_segment_stats_returns_empty_for_unknown_segment(): void
    {
        $stats = $this->service->getSegmentStats(99999);

        $this->assertEmpty($stats);
    }

    public function test_get_segment_stats_returns_member_count(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $segment = $this->makeSegment();
        $segment->members()->attach([$user1->id, $user2->id]);

        $stats = $this->service->getSegmentStats($segment->id);

        $this->assertArrayHasKey('total_members', $stats);
        $this->assertEquals(2, $stats['total_members']);
    }
}
