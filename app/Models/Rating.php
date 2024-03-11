<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    protected $table = 'ratings';

    protected $fillable = [
        'user_id', 'product_id', 'rating'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public static function calculateAverageRating($productId)
    {
        return self::where('product_id', $productId)
                    ->avg('rating');
    }
}
/**
 * Rating model represents the rating data and contains business logic for rating-related operations.
 */
    public static function calculateAverageRating($productId)
    {
        return self::where('product_id', $productId)
                    ->avg('rating');
    }
}
