<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'type',
        'value',
        'valid_from',
        'valid_until',
        'max_uses',
        'min_purchase_amount',
    ];

    protected $casts = [
        'valid_from' => 'datetime',
        'valid_until' => 'datetime',
        'value' => 'float',
        'max_uses' => 'integer',
        'min_purchase_amount' => 'float',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function isValid()
    {
        $now = now();
        return $this->valid_from <= $now && $this->valid_until >= $now && ($this->max_uses === null || $this->orders()->count() < $this->max_uses);
    }
}