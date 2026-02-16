<?php

namespace App\Models;

use App\Traits\IsTenantModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    use IsTenantModel;

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
        'is_dropshipping',
        'recipient_name',
        'recipient_email',
        'gift_message',
        'supplier_id',
        'supplier_reference',
        'supplier_response',
    ];

    protected $casts = [
        'supplier_response' => 'array',
        'is_dropshipping' => 'boolean',
        'shipping_cost' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
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
}
