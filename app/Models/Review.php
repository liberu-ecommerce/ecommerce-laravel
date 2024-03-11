<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $table = 'reviews';

    protected $fillable = [
        'user_id', 'product_id', 'rating', 'review', 'approved'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function approve()
    {
        $this->approved = true;
        $this->save();
    }

    public function reject()
    {
        $this->approved = false;
        $this->save();
    }
}
