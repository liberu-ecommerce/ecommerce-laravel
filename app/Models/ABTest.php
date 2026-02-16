<?php

namespace App\Models;

use App\Traits\IsTenantModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ABTest extends Model
{
    use HasFactory, IsTenantModel;

    protected $table = 'ab_tests';

    protected $fillable = [
        'name',
        'description',
        'type',
        'variants',
        'traffic_allocation',
        'starts_at',
        'ends_at',
        'status',
        'winning_variant',
    ];

    protected $casts = [
        'variants' => 'array',
        'traffic_allocation' => 'decimal:2',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function assignments(): HasMany
    {
        return $this->hasMany(ABTestAssignment::class, 'test_id');
    }

    /**
     * Assign a variant to a user/session
     */
    public function assignVariant(?int $userId, string $sessionId): ABTestAssignment
    {
        // Check if already assigned
        $existing = $this->assignments()
            ->where('session_id', $sessionId)
            ->first();

        if ($existing) {
            return $existing;
        }

        // Randomly assign a variant based on traffic allocation
        $variantName = $this->selectRandomVariant();

        return $this->assignments()->create([
            'user_id' => $userId,
            'session_id' => $sessionId,
            'variant_name' => $variantName,
            'assigned_at' => now(),
        ]);
    }

    /**
     * Select a random variant
     */
    protected function selectRandomVariant(): string
    {
        $variants = $this->variants;
        $totalWeight = array_sum(array_column($variants, 'weight'));
        $random = mt_rand(1, $totalWeight * 100) / 100;

        $cumulative = 0;
        foreach ($variants as $variant) {
            $cumulative += $variant['weight'];
            if ($random <= $cumulative) {
                return $variant['name'];
            }
        }

        return $variants[0]['name']; // Fallback
    }

    /**
     * Calculate conversion rate per variant
     */
    public function getConversionRates(): array
    {
        $stats = [];

        foreach ($this->variants as $variant) {
            $assignments = $this->assignments()->where('variant_name', $variant['name']);
            
            $total = $assignments->count();
            $converted = $assignments->where('converted', true)->count();
            $revenue = $assignments->sum('conversion_value');

            $stats[$variant['name']] = [
                'total' => $total,
                'converted' => $converted,
                'conversion_rate' => $total > 0 ? round(($converted / $total) * 100, 2) : 0,
                'revenue' => $revenue,
                'avg_value' => $converted > 0 ? round($revenue / $converted, 2) : 0,
            ];
        }

        return $stats;
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'running')
            ->where('starts_at', '<=', now())
            ->where(function ($q) {
                $q->whereNull('ends_at')
                  ->orWhere('ends_at', '>=', now());
            });
    }
}
