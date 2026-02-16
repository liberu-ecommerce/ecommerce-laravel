<?php

namespace App\Models;

use App\Traits\IsTenantModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CustomerSegment extends Model
{
    use HasFactory, IsTenantModel;

    protected $fillable = [
        'name',
        'description',
        'conditions',
        'match_type',
        'is_active',
        'customer_count',
        'last_calculated_at',
    ];

    protected $casts = [
        'conditions' => 'array',
        'is_active' => 'boolean',
        'customer_count' => 'integer',
        'last_calculated_at' => 'datetime',
    ];

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'customer_segment_members', 'segment_id', 'user_id')
            ->withTimestamps()
            ->withPivot('added_at');
    }

    /**
     * Calculate and update segment membership based on conditions
     */
    public function calculateMembers(): void
    {
        if (!$this->is_active) {
            return;
        }

        $query = User::query();
        
        foreach ($this->conditions as $condition) {
            $this->applyCondition($query, $condition);
        }

        $userIds = $query->pluck('id');
        
        // Sync members
        $this->members()->sync($userIds);
        
        // Update count and timestamp
        $this->update([
            'customer_count' => $userIds->count(),
            'last_calculated_at' => now(),
        ]);
    }

    /**
     * Apply a single condition to the query
     */
    protected function applyCondition($query, array $condition): void
    {
        $field = $condition['field'] ?? null;
        $operator = $condition['operator'] ?? '=';
        $value = $condition['value'] ?? null;

        if (!$field) {
            return;
        }

        // Handle different condition types
        match($field) {
            'total_orders' => $query->whereHas('orders', function($q) use ($operator, $value) {
                $q->havingRaw("COUNT(*) {$operator} ?", [$value]);
            }),
            'lifetime_value' => $query->whereHas('customerMetric', function($q) use ($operator, $value) {
                $q->where('lifetime_value', $operator, $value);
            }),
            'last_order_date' => $query->whereHas('orders', function($q) use ($operator, $value) {
                $q->latest()->limit(1)->where('created_at', $operator, $value);
            }),
            'has_purchased_product' => $query->whereHas('orders.items', function($q) use ($value) {
                $q->where('product_id', $value);
            }),
            'in_customer_group' => $query->where('customer_group_id', $value),
            default => null
        };
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
