<?php

/**
 * HomeController handles the requests for the home page of the ecommerce platform.
 * It passes the category index and the featured/latest product rails to the home view.
 */

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Displays the home page: the category index, featured products, and latest arrivals.
     *
     * @return View The home view populated with real catalogue data.
     */
    public function index(): View
    {
        // A category with nothing in it is a dead end, so it is never offered as a
        // way into the catalogue. The count is real data and gets rendered.
        //
        // whereHas, not having('products_count', '>', 0): withCount builds a
        // correlated subquery rather than an aggregate, so SQLite rejects HAVING
        // outright while MySQL would quietly accept it. EXISTS is portable.
        $categories = ProductCategory::query()
            ->withCount('products')
            ->whereHas('products')
            ->orderBy('name')
            ->get();

        // withCount/withAvg keep the card's rating block from firing two queries
        // per product across the grid.
        $withRatings = fn ($query) => $query->withCount('review')->withAvg('rating', 'rating');

        return view('home', [
            'categories' => $categories,
            'featuredProducts' => $withRatings(Product::where('is_featured', true))->take(6)->get(),
            'latestProducts' => $withRatings(Product::latest())->take(6)->get(),
        ]);
    }
}
