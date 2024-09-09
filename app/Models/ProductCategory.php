<?php

namespace App\Models;

use App\Traits\IsTenantModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ProductCategory extends Model
{
    use HasFactory;
    use IsTenantModel;

    protected $table = 'product_categories';

    protected $fillable = [
        'name',
        'meta_title',
        'meta_description',
        'meta_keywords',
    ];

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function getSlugAttribute()
    {
        return Str::slug($this->name);
    }
}
