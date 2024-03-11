<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $table = 'orders';

    protected $fillable = [
        'customer_id',
        'product_details',
        'quantities',
        'prices',
        'total_amount',
        'payment_status',
        'shipping_status',
        'order_status',
    ];

    public function customers()
    {
        return $this->belongsTo(Customer::class);
    }
}
