<?php

namespace App\Models;

use App\Interfaces\Orderable;
use App\Traits\IsTenantModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ProductCollection extends Model implements Orderable
{
    use HasFactory;
    use IsTenantModel;

    protected $table = "collections";

    protected $fillable = [
        'name',
        'description',
        'price',
    ];

    public function getPrice(): float
    {
        return $this->price;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'collection_items', 'collection_id')
            ->withPivot('quantity');
    }
}
