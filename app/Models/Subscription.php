<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    protected $fillable = [
        'name', 'stripe_id', 'stripe_status', 'stripe_plan', 'quantity', 'trial_ends_at', 'ends_at',
    ];

    protected $casts = [
        'trial_ends_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isActive(): bool
    {
        return $this->stripe_status === 'active';
    }

    // ponytail: removed cancel()/renew() — they called Cashier Billable methods
    // ($this->subscription('default'), $this->onGracePeriod(), ->resume()) that don't
    // exist on this model and depended on laravel/cashier, which is not installed.
    // Re-add real cancel/renew when a Stripe/Cashier billing integration is actually wired.
}
