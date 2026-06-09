<?php

namespace Tests\Unit;

use App\Models\LoyaltyProgram;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoyaltyProgramTest extends TestCase
{
    use RefreshDatabase;

    private function makeProgram(array $overrides = []): LoyaltyProgram
    {
        return LoyaltyProgram::create(array_merge([
            'name' => 'Rewards Program',
            'description' => 'Earn points on every purchase',
            'points_per_dollar' => 1.00,
            'points_value' => 0.01,
            'points_expiry_days' => 365,
            'min_points_redemption' => 100,
            'is_active' => true,
        ], $overrides));
    }

    public function test_calculate_points_earned_for_amount(): void
    {
        $program = $this->makeProgram(['points_per_dollar' => 2.00]);

        $points = $program->calculatePointsEarned(50.00);

        $this->assertEquals(100, $points);
    }

    public function test_calculate_points_earned_floors_fractional_points(): void
    {
        $program = $this->makeProgram(['points_per_dollar' => 1.5]);

        $points = $program->calculatePointsEarned(10.00);

        $this->assertEquals(15, $points);
    }

    public function test_calculate_points_earned_for_zero(): void
    {
        $program = $this->makeProgram();

        $points = $program->calculatePointsEarned(0.0);

        $this->assertEquals(0, $points);
    }

    public function test_calculate_points_value_for_points(): void
    {
        $program = $this->makeProgram(['points_value' => 0.01]);

        $value = $program->calculatePointsValue(100);

        $this->assertEquals(1.00, $value);
    }

    public function test_calculate_points_value_rounds_to_two_decimals(): void
    {
        $program = $this->makeProgram(['points_value' => 0.0075]);

        $value = $program->calculatePointsValue(100);

        $this->assertEquals(0.75, $value);
    }

    public function test_program_has_tiers_relationship(): void
    {
        $program = $this->makeProgram();

        $this->assertEmpty($program->tiers);
    }

    public function test_program_has_rewards_relationship(): void
    {
        $program = $this->makeProgram();

        $this->assertEmpty($program->rewards);
    }
}
