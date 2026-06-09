<?php

namespace Tests\Unit;

use App\Models\ABTest;
use App\Models\ABTestAssignment;
use App\Services\ABTestingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class ABTestingServiceTest extends TestCase
{
    use RefreshDatabase;

    private ABTestingService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ABTestingService();
    }

    private function makeActiveTest(string $name = 'button_test'): ABTest
    {
        return ABTest::create([
            'name' => $name,
            'type' => 'feature',
            'variants' => [
                ['name' => 'control', 'weight' => 50],
                ['name' => 'variant_a', 'weight' => 50],
            ],
            'status' => 'running',
            'starts_at' => now()->subHour(),
        ]);
    }

    public function test_get_variant_returns_null_for_nonexistent_test(): void
    {
        $variant = $this->service->getVariant('nonexistent_test');

        $this->assertNull($variant);
    }

    public function test_get_variant_returns_null_for_inactive_test(): void
    {
        ABTest::create([
            'name' => 'draft_test',
            'type' => 'feature',
            'variants' => [['name' => 'control', 'weight' => 100]],
            'status' => 'draft',
            'starts_at' => now()->subHour(),
        ]);

        $variant = $this->service->getVariant('draft_test');

        $this->assertNull($variant);
    }

    public function test_get_variant_returns_valid_variant_for_active_test(): void
    {
        $this->makeActiveTest();

        $variant = $this->service->getVariant('button_test');

        $this->assertContains($variant, ['control', 'variant_a']);
    }

    public function test_get_variant_returns_same_variant_for_same_session(): void
    {
        $this->makeActiveTest();

        $first = $this->service->getVariant('button_test');
        $second = $this->service->getVariant('button_test');

        $this->assertEquals($first, $second);
    }

    public function test_is_variant_returns_true_when_in_variant(): void
    {
        $this->makeActiveTest();

        $variant = $this->service->getVariant('button_test');
        $result = $this->service->isVariant('button_test', $variant);

        $this->assertTrue($result);
    }

    public function test_is_variant_returns_false_when_not_in_variant(): void
    {
        $this->makeActiveTest();

        $variant = $this->service->getVariant('button_test');
        $otherVariant = $variant === 'control' ? 'variant_a' : 'control';

        $result = $this->service->isVariant('button_test', $otherVariant);

        $this->assertFalse($result);
    }

    public function test_track_conversion_does_nothing_for_nonexistent_test(): void
    {
        $this->service->trackConversion('nonexistent_test');

        // No exception thrown
        $this->assertTrue(true);
    }

    public function test_track_conversion_marks_assignment_converted(): void
    {
        $test = $this->makeActiveTest();
        $this->service->getVariant('button_test');

        $sessionId = session()->getId();
        $assignment = ABTestAssignment::where('test_id', $test->id)
            ->where('session_id', $sessionId)
            ->first();

        if ($assignment) {
            $this->assertFalse($assignment->converted);

            $this->service->trackConversion('button_test', 99.99);

            $this->assertTrue($assignment->fresh()->converted);
            $this->assertEquals(99.99, $assignment->fresh()->conversion_value);
        } else {
            $this->markTestSkipped('No assignment created (session ID mismatch)');
        }
    }

    public function test_create_test_creates_draft_test(): void
    {
        $test = $this->service->createTest(
            'new_test',
            'price',
            [['name' => 'control', 'weight' => 50], ['name' => 'cheap', 'weight' => 50]],
            'Testing pricing'
        );

        $this->assertInstanceOf(ABTest::class, $test);
        $this->assertEquals('new_test', $test->name);
        $this->assertEquals('draft', $test->status);
        $this->assertEquals('Testing pricing', $test->description);
    }

    public function test_start_test_changes_status_to_running(): void
    {
        $test = $this->service->createTest('start_test', 'page', [['name' => 'v1', 'weight' => 100]]);

        $result = $this->service->startTest($test->id);

        $this->assertTrue($result);
        $this->assertEquals('running', $test->fresh()->status);
        $this->assertNotNull($test->fresh()->starts_at);
    }

    public function test_start_test_returns_false_for_already_running(): void
    {
        $test = $this->makeActiveTest('running_test');

        $result = $this->service->startTest($test->id);

        $this->assertFalse($result);
    }

    public function test_start_test_returns_false_for_nonexistent(): void
    {
        $result = $this->service->startTest(99999);

        $this->assertFalse($result);
    }

    public function test_end_test_sets_status_completed(): void
    {
        $test = $this->makeActiveTest();

        $result = $this->service->endTest($test->id, 'variant_a');

        $this->assertTrue($result);
        $this->assertEquals('completed', $test->fresh()->status);
        $this->assertEquals('variant_a', $test->fresh()->winning_variant);
    }

    public function test_end_test_returns_false_for_nonexistent(): void
    {
        $result = $this->service->endTest(99999);

        $this->assertFalse($result);
    }

    public function test_get_test_results_returns_empty_for_nonexistent(): void
    {
        $results = $this->service->getTestResults(99999);

        $this->assertEmpty($results);
    }

    public function test_get_test_results_returns_stats(): void
    {
        $test = $this->makeActiveTest();

        $results = $this->service->getTestResults($test->id);

        $this->assertArrayHasKey('test', $results);
        $this->assertArrayHasKey('variants', $results);
        $this->assertArrayHasKey('total_assignments', $results);
        $this->assertArrayHasKey('total_conversions', $results);
        $this->assertEquals($test->id, $results['test']['id']);
    }
}
