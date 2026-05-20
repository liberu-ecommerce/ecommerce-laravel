<?php

namespace App\Services;

use App\Models\ABTest;
use App\Models\ABTestAssignment;
use Illuminate\Support\Facades\Cookie;

class ABTestingService
{
    /**
     * Get variant for current user/session
     */
    public function getVariant(string $testName, ?int $userId = null): ?string
    {
        $test = ABTest::where('name', $testName)
            ->active()
            ->first();

        if (!$test) {
            return null;
        }

        $sessionId = $this->getSessionId();
        $assignment = $test->assignVariant($userId, $sessionId);

        // Store in cookie for consistent experience
        Cookie::queue("ab_test_{$test->id}", $assignment->variant_name, 60 * 24 * 30);

        return $assignment->variant_name;
    }

    /**
     * Check if user is in a specific variant
     */
    public function isVariant(string $testName, string $variantName, ?int $userId = null): bool
    {
        $variant = $this->getVariant($testName, $userId);
        return $variant === $variantName;
    }

    /**
     * Track conversion for current session
     */
    public function trackConversion(string $testName, ?float $value = null, ?int $userId = null): void
    {
        $test = ABTest::where('name', $testName)->first();

        if (!$test) {
            return;
        }

        $sessionId = $this->getSessionId();

        $assignment = ABTestAssignment::where('test_id', $test->id)
            ->where('session_id', $sessionId)
            ->first();

        if ($assignment && !$assignment->converted) {
            $assignment->markConverted($value);
        }
    }

    /**
     * Get test results
     */
    public function getTestResults(int $testId): array
    {
        $test = ABTest::find($testId);

        if (!$test) {
            return [];
        }

        return [
            'test' => $test->only(['id', 'name', 'description', 'type', 'status']),
            'variants' => $test->getConversionRates(),
            'total_assignments' => $test->assignments()->count(),
            'total_conversions' => $test->assignments()->where('converted', true)->count(),
        ];
    }

    /**
     * Create a new A/B test
     */
    public function createTest(
        string $name,
        string $type,
        array $variants,
        ?string $description = null
    ): ABTest {
        return ABTest::create([
            'name' => $name,
            'description' => $description,
            'type' => $type,
            'variants' => $variants,
            'status' => 'draft',
        ]);
    }

    /**
     * Start a test
     */
    public function startTest(int $testId): bool
    {
        $test = ABTest::find($testId);

        if (!$test || $test->status !== 'draft') {
            return false;
        }

        $test->update([
            'status' => 'running',
            'starts_at' => now(),
        ]);

        return true;
    }

    /**
     * End a test and declare winner
     */
    public function endTest(int $testId, ?string $winningVariant = null): bool
    {
        $test = ABTest::find($testId);

        if (!$test) {
            return false;
        }

        $test->update([
            'status' => 'completed',
            'ends_at' => now(),
            'winning_variant' => $winningVariant,
        ]);

        return true;
    }

    /**
     * Get or generate session ID
     */
    protected function getSessionId(): string
    {
        return session()->getId() ?: request()->ip();
    }
}
