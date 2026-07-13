<?php

namespace App\Models;

use App\Traits\IsTenantModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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
            ->withPivot('added_at');
    }

    /**
     * Calculate and update segment membership based on conditions
     */
    public function calculateMembers(): void
    {
        if (! $this->is_active) {
            return;
        }

        $query = User::query();

        // match_type 'any' => OR the conditions, 'all' => AND them.
        // Each condition is its own nested group so whereHas/has clauses combine correctly.
        $boolean = $this->match_type === 'any' ? 'orWhere' : 'where';

        foreach ($this->conditions as $condition) {
            $query->{$boolean}(function ($q) use ($condition) {
                $this->applyCondition($q, $condition);
            });
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

        if (! $field) {
            return;
        }

        // Handle different condition types
        match ($field) {
            // has() builds a correlated `(select count(*) ...) <op> <value>` predicate.
            // The old havingRaw()-in-whereHas produced invalid SQL ("HAVING on a non-aggregate query").
            'total_orders' => $query->has('orders', $operator, (int) $value),
            'lifetime_value' => $query->whereHas('customerMetric', function ($q) use ($operator, $value) {
                $q->where('lifetime_value', $operator, $value);
            }),
            // Compare the user's MOST RECENT order date. whereHas() compiles to an
            // EXISTS subquery where latest()/limit() are inert — it would match a
            // user with ANY qualifying order, which is wrong for <=/</=. Use a
            // correlated MAX() so the comparison is against the last order only.
            'last_order_date' => $query->whereRaw(
                '(select max(created_at) from orders where orders.user_id = users.id) '.$this->safeOperator($operator).' ?',
                [$value]
            ),
            'has_purchased_product' => $query->whereHas('orders.items', function ($q) use ($value) {
                $q->where('product_id', $value);
            }),
            'in_customer_group' => $query->where('customer_group_id', $value),
            // Fail closed: an unrecognised field must not silently drop the filter
            // (which would match EVERY user under match_type=all) — match no one.
            default => $query->whereRaw('1 = 0'),
        };
    }

    /** Whitelist comparison operators — condition config must never reach raw SQL unchecked. */
    private function safeOperator(string $operator): string
    {
        return in_array($operator, ['=', '!=', '<', '<=', '>', '>='], true) ? $operator : '=';
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
