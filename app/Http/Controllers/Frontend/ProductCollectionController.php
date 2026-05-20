<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductCollection;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class ProductCollectionController extends Controller
{
    public function index()
    {
        $collections = QueryBuilder::for(ProductCollection::class)
            ->allowedFilters(['name'])
            ->allowedSorts(['name', 'created_at'])
            ->paginate();

        return view('collections.index', compact('collections'));
    }

    public function show(ProductCollection $collection)
    {
        return view('collections.show', compact('collection'));
    }

    public function products(Request $request, ProductCollection $collection)
    {
        $products = QueryBuilder::for($collection->products())
            ->allowedFilters([
                'name',
                'price',
                'created_at',
                AllowedFilter::scope('price_min'),
                AllowedFilter::scope('price_max'),
            ])
            ->allowedSorts(['name', 'price', 'created_at'])
            ->paginate(config('pagination.per_page'))
            ->appends($request->query());

        return view('collections.products', compact('collection', 'products'));
    }
}
