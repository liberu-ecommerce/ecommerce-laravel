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
