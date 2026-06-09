<?php

namespace Tests\Unit;

use App\Models\LoyaltyProgram;
use App\Models\LoyaltyTier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoyaltyTierModelTest extends TestCase
{
    use RefreshDatabase;

    private LoyaltyProgram $program;

    protected function setUp(): void
    {
        parent::setUp();
        $this->program = LoyaltyProgram::create([
            'name' => 'Tier Test Program',
            'points_per_dollar' => 1,
            'points_value' => 0.01,
            'is_active' => true,
        ]);
    }

    private function makeTier(array $overrides = []): LoyaltyTier
    {
        return LoyaltyTier::create(array_merge([
            'loyalty_program_id' => $this->program->id,
            'name' => 'Gold',
            'min_points' => 1000,
            'min_spend' => 500.00,
            'points_multiplier' => 1.5,
            'discount_percentage' => 5.00,
            'sort_order' => 1,
        ], $overrides));
    }

    public function test_loyalty_tier_can_be_created(): void
    {
        $tier = $this->makeTier();

        $this->assertInstanceOf(LoyaltyTier::class, $tier);
        $this->assertEquals('Gold', $tier->name);
    }

    public function test_qualifies_for_tier_by_points(): void
    {
        $tier = $this->makeTier(['min_points' => 500, 'min_spend' => 999999]);

        $this->assertTrue($tier->qualifiesForTier(600, 0));
        $this->assertFalse($tier->qualifiesForTier(400, 0));
    }

    public function test_qualifies_for_tier_by_spend(): void
    {
        $tier = $this->makeTier(['min_points' => 999999, 'min_spend' => 200.00]);

        $this->assertTrue($tier->qualifiesForTier(0, 250.00));
        $this->assertFalse($tier->qualifiesForTier(0, 150.00));
    }

    public function test_belongs_to_program(): void
    {
        $tier = $this->makeTier();

        $this->assertInstanceOf(LoyaltyProgram::class, $tier->program);
        $this->assertEquals($this->program->id, $tier->program->id);
    }

    public function test_casts_are_applied(): void
    {
        $tier = $this->makeTier(['min_points' => 100, 'sort_order' => 2]);
        $fresh = $tier->fresh();

        $this->assertIsInt($fresh->min_points);
        $this->assertIsInt($fresh->sort_order);
    }
}
