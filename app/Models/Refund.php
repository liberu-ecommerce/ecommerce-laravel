<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Refund extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'amount',
        'reason',
        'notes',
        'status',
        'refund_method',
        'transaction_id',
        'processed_by',
        'processed_at',
        'restock_items',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'processed_at' => 'datetime',
        'restock_items' => 'boolean',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(RefundItem::class);
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    /**
     * Process the refund
     */
    public function process(?int $userId = null): bool
    {
        if ($this->status !== 'pending') {
            return false;
        }

        $this->update([
            'status' => 'processed',
            'processed_by' => $userId,
            'processed_at' => now(),
        ]);

        // Update order refund totals
        $this->order->increment('refund_total', $this->amount);
        $this->order->update([
            'partially_refunded' => $this->order->refund_total < $this->order->total,
            'fully_refunded' => $this->order->refund_total >= $this->order->total,
        ]);

        // Restock items if needed
        if ($this->restock_items) {
            foreach ($this->items as $item) {
                if ($item->restock) {
                    $item->orderItem->product->increment('inventory_count', $item->quantity);
                }
            }
        }

        return true;
    }
}
