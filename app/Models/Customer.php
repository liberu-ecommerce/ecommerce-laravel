<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $table = 'customers';

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone_number',
        'address',
        'city',
        'state',
        'postal_code',
    ];

    public function orders()
    {
        return $this->hasMany(Orders::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function review()
    {
        return $this->hasMany(Review::class);
    }

    public function rating()
    {
        return $this->hasMany(Rating::class);
    }
}
