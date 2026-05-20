<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LoyaltyReward extends Model
{
    use HasFactory;

    protected $fillable = [
        'loyalty_program_id',
        'name',
        'description',
        'reward_type',
        'discount_value',
        'free_product_id',
        'points_cost',
        'max_redemptions',
        'stock_quantity',
        'is_active',
        'available_from',
        'available_until',
    ];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'points_cost' => 'integer',
        'max_redemptions' => 'integer',
        'stock_quantity' => 'integer',
        'is_active' => 'boolean',
        'available_from' => 'datetime',
        'available_until' => 'datetime',
    ];

    public function program(): BelongsTo
    {
        return $this->belongsTo(LoyaltyProgram::class, 'loyalty_program_id');
    }

    public function freeProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'free_product_id');
    }

    public function redemptions(): HasMany
    {
        return $this->hasMany(LoyaltyRewardRedemption::class);
    }

    /**
     * Check if reward is available
     */
    public function isAvailable(?int $userId = null): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->available_from && now()->lt($this->available_from)) {
            return false;
        }

        if ($this->available_until && now()->gt($this->available_until)) {
            return false;
        }

        if ($this->stock_quantity !== null && $this->stock_quantity <= 0) {
            return false;
        }

        if ($userId && $this->max_redemptions) {
            $userRedemptions = $this->redemptions()
                ->where('user_id', $userId)
                ->where('status', '!=', 'cancelled')
                ->count();

            if ($userRedemptions >= $this->max_redemptions) {
                return false;
            }
        }

        return true;
    }

    /**
     * Redeem reward for user
     */
    public function redeem(int $userId, ?int $orderId = null): ?LoyaltyRewardRedemption
    {
        if (!$this->isAvailable($userId)) {
            return null;
        }

        // Check user has enough points
        $loyaltyPoints = LoyaltyPoints::where('user_id', $userId)
            ->where('loyalty_program_id', $this->loyalty_program_id)
            ->first();

        if (!$loyaltyPoints || $loyaltyPoints->balance < $this->points_cost) {
            return null;
        }

        // Redeem points
        $loyaltyPoints->redeemPoints($this->points_cost, "Redeemed: {$this->name}", $orderId);

        // Decrement stock
        if ($this->stock_quantity !== null) {
            $this->decrement('stock_quantity');
        }

        // Create redemption record
        return $this->redemptions()->create([
            'user_id' => $userId,
            'order_id' => $orderId,
            'points_spent' => $this->points_cost,
            'status' => 'pending',
        ]);
    }
}
