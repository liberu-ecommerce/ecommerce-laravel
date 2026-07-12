<?php

namespace Tests\Unit;

use App\Models\ABTest;
use App\Models\ABTestAssignment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ABTestModelTest extends TestCase
{
    use RefreshDatabase;

    private function makeTest(array $overrides = []): ABTest
    {
        return ABTest::create(array_merge([
            'name' => 'test_button_color',
            'description' => 'Test button color',
            'type' => 'feature',
            'variants' => [
                ['name' => 'control', 'weight' => 50],
                ['name' => 'variant_a', 'weight' => 50],
            ],
            'status' => 'running',
            'starts_at' => now()->subHour(),
        ], $overrides));
    }

    public function test_active_scope_returns_running_tests(): void
    {
        $active = $this->makeTest(['status' => 'running', 'starts_at' => now()->subHour()]);
        $draft = $this->makeTest(['status' => 'draft', 'name' => 'test_draft', 'type' => 'page']);

        $results = ABTest::active()->get();

        $this->assertTrue($results->contains('id', $active->id));
        $this->assertFalse($results->contains('id', $draft->id));
    }

    public function test_active_scope_excludes_future_tests(): void
    {
        $future = $this->makeTest(['starts_at' => now()->addHour(), 'name' => 'future_test', 'type' => 'page']);

        $results = ABTest::active()->get();

        $this->assertFalse($results->contains('id', $future->id));
    }

    public function test_active_scope_excludes_expired_tests(): void
    {
        $expired = $this->makeTest([
            'name' => 'expired_test',
            'type' => 'page',
            'ends_at' => now()->subHour(),
        ]);

        $results = ABTest::active()->get();

        $this->assertFalse($results->contains('id', $expired->id));
    }

    public function test_assign_variant_creates_new_assignment(): void
    {
        $test = $this->makeTest();

        $assignment = $test->assignVariant(null, 'session_abc');

        $this->assertInstanceOf(ABTestAssignment::class, $assignment);
        $this->assertEquals('session_abc', $assignment->session_id);
        $this->assertContains($assignment->variant_name, ['control', 'variant_a']);
    }

    public function test_assign_variant_returns_existing_for_same_session(): void
    {
        $test = $this->makeTest();

        $first = $test->assignVariant(null, 'session_xyz');
        $second = $test->assignVariant(null, 'session_xyz');

        $this->assertEquals($first->id, $second->id);
        $this->assertEquals($first->variant_name, $second->variant_name);
    }

    public function test_assign_variant_stores_user_id(): void
    {
        $test = $this->makeTest();
        $user = User::factory()->create();

        $assignment = $test->assignVariant($user->id, 'session_user');

        $this->assertEquals($user->id, $assignment->user_id);
    }

    public function test_assign_variant_is_stable_per_user_across_sessions(): void
    {
        $test = $this->makeTest();
        $user = User::factory()->create();

        // Same user, two different sessions (e.g. new device / cleared cookies)
        $first = $test->assignVariant($user->id, 'session_one');
        $second = $test->assignVariant($user->id, 'session_two');

        $this->assertEquals($first->id, $second->id);
        $this->assertEquals($first->variant_name, $second->variant_name);
        $this->assertEquals(
            1,
            ABTestAssignment::where('test_id', $test->id)->where('user_id', $user->id)->count()
        );
    }

    public function test_get_conversion_rates_returns_stats_per_variant(): void
    {
        $test = $this->makeTest();

        $test->assignments()->create([
            'session_id' => 'sess_1',
            'variant_name' => 'control',
            'assigned_at' => now(),
            'converted' => false,
        ]);
        $test->assignments()->create([
            'session_id' => 'sess_2',
            'variant_name' => 'control',
            'assigned_at' => now(),
            'converted' => true,
            'conversion_value' => 100.00,
        ]);
        $test->assignments()->create([
            'session_id' => 'sess_3',
            'variant_name' => 'variant_a',
            'assigned_at' => now(),
            'converted' => false,
        ]);

        $rates = $test->getConversionRates();

        $this->assertArrayHasKey('control', $rates);
        $this->assertArrayHasKey('variant_a', $rates);
        $this->assertEquals(2, $rates['control']['total']);
        $this->assertEquals(1, $rates['control']['converted']);
        $this->assertEquals(50.00, $rates['control']['conversion_rate']);
        $this->assertEquals(1, $rates['variant_a']['total']);
        $this->assertEquals(0, $rates['variant_a']['converted']);
    }

    public function test_assignments_relationship(): void
    {
        $test = $this->makeTest();
        $test->assignments()->create([
            'session_id' => 'sess_rel',
            'variant_name' => 'control',
            'assigned_at' => now(),
        ]);

        $this->assertEquals(1, $test->assignments()->count());
    }
}
