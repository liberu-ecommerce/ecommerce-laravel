<?php

namespace App\Models;

use App\Traits\IsTenantModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomerGroup extends Model
{
    use HasFactory;
    use IsTenantModel;

    protected $fillable = [
        'name',
        'description',
        'discount_percentage',
        'discount_amount',
        'minimum_order_amount',
        'free_shipping_threshold',
        'is_active',
        'conditions',
        'benefits',
    ];

    protected $casts = [
        'discount_percentage' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'minimum_order_amount' => 'decimal:2',
        'free_shipping_threshold' => 'decimal:2',
        'is_active' => 'boolean',
        'conditions' => 'array',
        'benefits' => 'array',
    ];

    public function customers(): BelongsToMany
    {
        return $this->belongsToMany(Customer::class, 'customer_group_memberships')
                    ->withPivot(['joined_at', 'expires_at'])
                    ->withTimestamps();
    }

    public function discounts(): HasMany
    {
        return $this->hasMany(Discount::class);
    }

    public function addCustomer(Customer $customer, $expiresAt = null): void
    {
        $this->customers()->attach($customer->id, [
            'joined_at' => now(),
            'expires_at' => $expiresAt,
        ]);
    }

    public function removeCustomer(Customer $customer): void
    {
        $this->customers()->detach($customer->id);
    }

    public function hasCustomer(Customer $customer): bool
    {
        return $this->customers()->where('customer_id', $customer->id)->exists();
    }

    public function getActiveCustomersCount(): int
    {
        return $this->customers()
                    ->wherePivot('expires_at', '>', now())
                    ->orWherePivotNull('expires_at')
                    ->count();
    }

    public function calculateDiscount(float $orderAmount): float
    {
        if (!$this->is_active || $orderAmount < $this->minimum_order_amount) {
            return 0;
        }

        if ($this->discount_percentage > 0) {
            return $orderAmount * ($this->discount_percentage / 100);
        }

        return min($this->discount_amount, $orderAmount);
    }

    public function qualifiesForFreeShipping(float $orderAmount): bool
    {
        return $this->free_shipping_threshold > 0 && $orderAmount >= $this->free_shipping_threshold;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeWithActiveMembers($query)
    {
        return $query->whereHas('customers', function ($q) {
            $q->where('expires_at', '>', now())
              ->orWhereNull('expires_at');
        });
    }
}