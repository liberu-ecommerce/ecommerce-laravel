<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WholesalePriceTier extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'product_variant_id',
        'wholesale_group_id',
        'min_quantity',
        'max_quantity',
        'price',
        'discount_percentage',
    ];

    protected $casts = [
        'min_quantity' => 'integer',
        'max_quantity' => 'integer',
        'price' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function wholesaleGroup(): BelongsTo
    {
        return $this->belongsTo(WholesaleGroup::class);
    }

    /**
     * Get applicable price for quantity
     */
    public static function getPriceForQuantity(
        int $productId,
        int $quantity,
        ?int $variantId = null,
        ?int $wholesaleGroupId = null
    ): ?float {
        $query = static::where('product_id', $productId)
            ->where('min_quantity', '<=', $quantity)
            ->where(function ($q) use ($quantity) {
                $q->whereNull('max_quantity')
                    ->orWhere('max_quantity', '>=', $quantity);
            });

        if ($variantId) {
            $query->where('product_variant_id', $variantId);
        }

        if ($wholesaleGroupId) {
            $query->where('wholesale_group_id', $wholesaleGroupId);
        }

        $tier = $query->orderBy('min_quantity', 'desc')->first();

        return $tier ? (float) $tier->price : null;
    }
}
