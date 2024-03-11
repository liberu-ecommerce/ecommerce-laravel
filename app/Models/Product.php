<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $table = 'products';

    protected $fillable = [
        'name',
        'description',
        'price',
        'category',
        'inventory_count',
    ];

    public function categories()
    {
        return $this->belongsTo(ProductCategory::class);
    }

    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function review()
    {
        return $this->hasMany(ProductReview::class);
    }

    public function rating()
    {
        return $this->hasMany(ProductRating::class);
    }


}
/**
 * This file contains the Product model, which represents the product entity in the database.
 */
    /**
     * Get the category that the product belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    /**
     * Get the cart items associated with the product.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    /**
     * Get the order items associated with the product.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    /**
     * Get the reviews associated with the product.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    /**
     * Get the ratings associated with the product.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
