<?php

namespace Tests\Unit;

use App\Models\LoyaltyPoints;
use App\Models\LoyaltyProgram;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoyaltyPointsTest extends TestCase
{
    use RefreshDatabase;

    private LoyaltyProgram $program;

    protected function setUp(): void
    {
        parent::setUp();
        $this->program = LoyaltyProgram::create([
            'name' => 'Test Program',
            'points_per_dollar' => 1.0,
            'points_value' => 0.01,
            'points_expiry_days' => 365,
            'min_points_redemption' => 100,
            'is_active' => true,
        ]);
    }

    private function makePoints(array $overrides = []): LoyaltyPoints
    {
        $user = User::factory()->create();
        return LoyaltyPoints::create(array_merge([
            'user_id' => $user->id,
            'loyalty_program_id' => $this->program->id,
            'balance' => 0,
            'lifetime_earned' => 0,
            'lifetime_redeemed' => 0,
        ], $overrides));
    }

    public function test_add_points_increases_balance(): void
    {
        $points = $this->makePoints();

        $points->addPoints(50, 'purchase', 'Order #1');

        $this->assertEquals(50, $points->fresh()->balance);
        $this->assertEquals(50, $points->fresh()->lifetime_earned);
    }

    public function test_add_points_creates_transaction(): void
    {
        $points = $this->makePoints();

        $points->addPoints(100, 'purchase', 'Order #2');

        $this->assertCount(1, $points->transactions);
        $this->assertEquals(100, $points->transactions->first()->points);
        $this->assertEquals('purchase', $points->transactions->first()->type);
    }

    public function test_redeem_points_decreases_balance(): void
    {
        $points = $this->makePoints(['balance' => 200, 'lifetime_earned' => 200]);

        $result = $points->redeemPoints(100, 'Coupon redemption');

        $this->assertTrue($result);
        $this->assertEquals(100, $points->fresh()->balance);
        $this->assertEquals(100, $points->fresh()->lifetime_redeemed);
    }

    public function test_redeem_points_returns_false_when_insufficient(): void
    {
        $points = $this->makePoints(['balance' => 50]);

        $result = $points->redeemPoints(100, 'test');

        $this->assertFalse($result);
        $this->assertEquals(50, $points->fresh()->balance);
    }

    public function test_redeem_points_creates_negative_transaction(): void
    {
        $points = $this->makePoints(['balance' => 200, 'lifetime_earned' => 200]);

        $points->redeemPoints(50, 'test');

        $transaction = $points->transactions()->first();
        $this->assertEquals(-50, $transaction->points);
        $this->assertEquals('redeemed', $transaction->type);
    }

    public function test_points_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $points = LoyaltyPoints::create([
            'user_id' => $user->id,
            'loyalty_program_id' => $this->program->id,
            'balance' => 0,
            'lifetime_earned' => 0,
            'lifetime_redeemed' => 0,
        ]);

        $this->assertInstanceOf(User::class, $points->user);
        $this->assertEquals($user->id, $points->user->id);
    }

    public function test_points_belongs_to_program(): void
    {
        $points = $this->makePoints();

        $this->assertInstanceOf(LoyaltyProgram::class, $points->program);
        $this->assertEquals($this->program->id, $points->program->id);
    }
}
