<?php

namespace Tests\Unit;

use App\Models\LoyaltyPoints;
use App\Models\LoyaltyPointTransaction;
use App\Models\LoyaltyProgram;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoyaltyPointTransactionModelTest extends TestCase
{
    use RefreshDatabase;

    private LoyaltyPoints $loyaltyPoints;

    protected function setUp(): void
    {
        parent::setUp();
        $program = LoyaltyProgram::create([
            'name' => 'Transaction Test Program',
            'points_per_dollar' => 1,
            'points_value' => 0.01,
            'is_active' => true,
        ]);
        $user = User::factory()->create();
        $this->loyaltyPoints = LoyaltyPoints::create([
            'user_id' => $user->id,
            'loyalty_program_id' => $program->id,
            'balance' => 200,
        ]);
    }

    public function test_transaction_can_be_created(): void
    {
        $txn = LoyaltyPointTransaction::create([
            'loyalty_points_id' => $this->loyaltyPoints->id,
            'points' => 50,
            'type' => 'earn',
            'description' => 'Earned from purchase',
        ]);

        $this->assertInstanceOf(LoyaltyPointTransaction::class, $txn);
        $this->assertEquals(50, $txn->points);
    }

    public function test_points_is_integer_cast(): void
    {
        $txn = LoyaltyPointTransaction::create([
            'loyalty_points_id' => $this->loyaltyPoints->id,
            'points' => 100,
            'type' => 'earn',
        ]);

        $this->assertIsInt($txn->fresh()->points);
    }

    public function test_is_expired_is_boolean_cast(): void
    {
        $txn = LoyaltyPointTransaction::create([
            'loyalty_points_id' => $this->loyaltyPoints->id,
            'points' => 25,
            'type' => 'earn',
            'is_expired' => false,
        ]);

        $this->assertIsBool($txn->fresh()->is_expired);
    }

    public function test_belongs_to_loyalty_points(): void
    {
        $txn = LoyaltyPointTransaction::create([
            'loyalty_points_id' => $this->loyaltyPoints->id,
            'points' => 75,
            'type' => 'redeem',
        ]);

        $this->assertInstanceOf(LoyaltyPoints::class, $txn->loyaltyPoints);
        $this->assertEquals($this->loyaltyPoints->id, $txn->loyaltyPoints->id);
    }
}
