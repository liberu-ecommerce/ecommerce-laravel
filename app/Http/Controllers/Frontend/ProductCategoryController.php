<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class ProductCategoryController extends Controller
{
    public function index()
    {
        $categories = QueryBuilder::for(ProductCategory::class)
            ->allowedFilters(['name'])
            ->allowedSorts(['name', 'created_at'])
            ->paginate();

        return view('categories.index', compact('categories'));
    }

    public function show(ProductCategory $category)
    {
        return view('categories.show', compact('category'));
    }

    public function products(Request $request, ProductCategory $category)
    {
        $products = QueryBuilder::for(Product::class)
            ->allowedFilters([
                'name',
                'price',
                'created_at',
                AllowedFilter::scope('price_min'),
                AllowedFilter::scope('price_max'),
            ])
            ->where('category_id', $category->id)
            ->allowedSorts(['name', 'price', 'created_at'])
            ->paginate(config('pagination.per_page'))
            ->appends($request->query());

        return view('categories.products', compact('category', 'products'));
    }
}
