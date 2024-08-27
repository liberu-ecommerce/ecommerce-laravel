<?php
/**
 * HomeController handles the requests for the home page of the ecommerce platform.
 * It fetches and passes featured products, latest products, and special offers to the home view.
 */

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Displays the home page with featured products, latest products, and special offers.
     *
     * @return \Illuminate\View\View The home view populated with products and offers.
     */
    public function index()
    {
        $featuredProducts = Product::where('is_featured', true)->take(6)->get();
        $latestProducts = Product::latest()->take(6)->get();
        // Assuming 'specialOffers' is a method or scope on the Product model that retrieves special offers
        // $specialOffers = Product::specialOffers()->get();

        return view('home', [
            'featuredProducts' => $featuredProducts,
            'latestProducts' => $latestProducts,
            'specialOffers' => [], //$specialOffers,
        ]);
    }
}
