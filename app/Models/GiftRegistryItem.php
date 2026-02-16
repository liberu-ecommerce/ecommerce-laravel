<?php

namespace App\Models;

use App\Traits\IsTenantModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GiftRegistryItem extends Model
{
    use HasFactory, IsTenantModel;

    protected $fillable = [
        'registry_id',
        'product_id',
        'product_variant_id',
        'quantity_requested',
        'quantity_purchased',
        'priority',
        'notes',
    ];

    protected $casts = [
        'quantity_requested' => 'integer',
        'quantity_purchased' => 'integer',
        'priority' => 'integer',
    ];

    public function registry(): BelongsTo
    {
        return $this->belongsTo(GiftRegistry::class, 'registry_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(GiftRegistryPurchase::class);
    }

    /**
     * Get remaining quantity needed
     */
    public function getRemainingQuantity(): int
    {
        return max(0, $this->quantity_requested - $this->quantity_purchased);
    }

    /**
     * Check if item is fully purchased
     */
    public function isFullyPurchased(): bool
    {
        return $this->quantity_purchased >= $this->quantity_requested;
    }

    /**
     * Mark quantity as purchased
     */
    public function markPurchased(int $quantity, int $orderId, ?string $purchaserName = null, ?string $purchaserEmail = null, bool $anonymous = false): GiftRegistryPurchase
    {
        $this->increment('quantity_purchased', $quantity);

        return $this->purchases()->create([
            'order_id' => $orderId,
            'quantity' => $quantity,
            'purchaser_name' => $purchaserName,
            'purchaser_email' => $purchaserEmail,
            'anonymous' => $anonymous,
            'purchased_at' => now(),
        ]);
    }
}
