<?php

namespace App\Models;

use App\Exceptions\InvalidOrderTransitionException;
use App\Traits\IsTenantModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;
    use IsTenantModel;

    public const STATUS_PENDING = 'pending';
    public const STATUS_PAID = 'paid';
    public const STATUS_FAILED = 'failed';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_SUPPLIER_QUEUED = 'supplier_queued';
    public const STATUS_SUPPLIER_FAILED = 'supplier_failed';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_REFUNDED = 'refunded';
    public const STATUS_PARTIALLY_REFUNDED = 'partially_refunded';

    /**
     * Allowed status transitions. A status maps to the set it may move to;
     * anything else is rejected by transitionTo(). Terminal states map to [].
     */
    public const TRANSITIONS = [
        self::STATUS_PENDING => [self::STATUS_PAID, self::STATUS_FAILED, self::STATUS_CANCELLED],
        self::STATUS_PAID => [
            self::STATUS_PROCESSING, self::STATUS_SUPPLIER_QUEUED, self::STATUS_SUPPLIER_FAILED,
            self::STATUS_COMPLETED, self::STATUS_CANCELLED, self::STATUS_REFUNDED, self::STATUS_PARTIALLY_REFUNDED,
        ],
        self::STATUS_PROCESSING => [
            self::STATUS_COMPLETED, self::STATUS_SUPPLIER_QUEUED, self::STATUS_CANCELLED,
            self::STATUS_REFUNDED, self::STATUS_PARTIALLY_REFUNDED,
        ],
        self::STATUS_SUPPLIER_QUEUED => [
            self::STATUS_SUPPLIER_FAILED, self::STATUS_PROCESSING, self::STATUS_COMPLETED, self::STATUS_REFUNDED,
        ],
        self::STATUS_SUPPLIER_FAILED => [self::STATUS_SUPPLIER_QUEUED, self::STATUS_CANCELLED],
        self::STATUS_COMPLETED => [self::STATUS_REFUNDED, self::STATUS_PARTIALLY_REFUNDED],
        self::STATUS_PARTIALLY_REFUNDED => [self::STATUS_REFUNDED],
        self::STATUS_FAILED => [],
        self::STATUS_CANCELLED => [],
        self::STATUS_REFUNDED => [],
    ];

    protected $table = 'orders';

    protected $fillable = [
        'customer_id',
        'customer_email',
        'order_date',
        'total_amount',
        'shipping_cost',
        'tax_amount',
        'discount_amount',
        'coupon_code',
        'payment_status',
        'shipping_status',
        'shipping_address',
        'shipping_method_id',
        'payment_method',
        'status',
        'is_dropshipped',
        'recipient_name',
        'recipient_email',
        'gift_message',
        'supplier_id',
        'supplier_order_reference',
        'supplier_tracking_number',
        'supplier_response',
        'transaction_id',
        'refund_total',
        'partially_refunded',
        'fully_refunded',
    ];

    protected $casts = [
        'supplier_response' => 'array',
        'is_dropshipped' => 'boolean',
        'shipping_cost' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'refund_total' => 'decimal:2',
        'partially_refunded' => 'boolean',
        'fully_refunded' => 'boolean',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function shippingMethod()
    {
        return $this->belongsTo(ShippingMethod::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class);
    }

    /**
     * Move the order to a new status, enforcing the allowed-transition map and
     * recording an audit row. Throws on an illegal transition (nothing changes).
     */
    public function transitionTo(string $status, ?int $changedBy = null, ?string $notes = null): void
    {
        $from = $this->status;

        if (! in_array($status, self::TRANSITIONS[$from] ?? [], true)) {
            throw new InvalidOrderTransitionException($from, $status);
        }

        $this->update(['status' => $status]);

        $this->statusHistory()->create([
            'from_status' => $from,
            'to_status' => $status,
            'changed_by' => $changedBy,
            'notes' => $notes,
        ]);
    }
}
