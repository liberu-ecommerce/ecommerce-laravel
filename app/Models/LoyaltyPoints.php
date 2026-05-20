<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LoyaltyPoints extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'loyalty_program_id',
        'balance',
        'lifetime_earned',
        'lifetime_redeemed',
    ];

    protected $casts = [
        'balance' => 'integer',
        'lifetime_earned' => 'integer',
        'lifetime_redeemed' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(LoyaltyProgram::class, 'loyalty_program_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(LoyaltyPointTransaction::class);
    }

    /**
     * Add points
     */
    public function addPoints(int $points, string $type, ?string $description = null, ?int $orderId = null): void
    {
        $this->increment('balance', $points);
        $this->increment('lifetime_earned', $points);

        $expiresAt = null;
        if ($this->program->points_expiry_days) {
            $expiresAt = now()->addDays($this->program->points_expiry_days);
        }

        $this->transactions()->create([
            'points' => $points,
            'type' => $type,
            'description' => $description,
            'order_id' => $orderId,
            'expires_at' => $expiresAt,
        ]);
    }

    /**
     * Redeem points
     */
    public function redeemPoints(int $points, ?string $description = null, ?int $orderId = null): bool
    {
        if ($this->balance < $points) {
            return false;
        }

        $this->decrement('balance', $points);
        $this->increment('lifetime_redeemed', $points);

        $this->transactions()->create([
            'points' => -$points,
            'type' => 'redeemed',
            'description' => $description,
            'order_id' => $orderId,
        ]);

        return true;
    }

    /**
     * Expire old points
     */
    public function expirePoints(): void
    {
        $expiredTransactions = $this->transactions()
            ->where('type', 'earned')
            ->where('expires_at', '<=', now())
            ->where('is_expired', false)
            ->get();

        foreach ($expiredTransactions as $transaction) {
            $this->decrement('balance', $transaction->points);
            $transaction->update(['is_expired' => true]);

            $this->transactions()->create([
                'points' => -$transaction->points,
                'type' => 'expired',
                'description' => 'Points expired',
            ]);
        }
    }
}
