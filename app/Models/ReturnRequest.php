<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReturnRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'customer_id',
        'rma_number',
        'reason',
        'description',
        'status',
        'return_method',
        'tracking_number',
        'approved_by',
        'approved_at',
        'received_at',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'received_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($returnRequest) {
            if (!$returnRequest->rma_number) {
                $returnRequest->rma_number = 'RMA-' . strtoupper(uniqid());
            }
        });
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ReturnRequestItem::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Approve the return request
     */
    public function approve(?int $userId = null): void
    {
        $this->update([
            'status' => 'approved',
            'approved_by' => $userId,
            'approved_at' => now(),
        ]);
    }

    /**
     * Mark as received
     */
    public function markAsReceived(): void
    {
        $this->update([
            'status' => 'received',
            'received_at' => now(),
        ]);
    }
}
