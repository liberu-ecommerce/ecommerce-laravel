&lt;?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $featuredProducts = Product::where('is_featured', true)->get();
        // Assuming 'specialOffers' is a method or scope on the Product model that retrieves special offers
        $specialOffers = Product::specialOffers()->get();

        return view('home', [
            'products' => $featuredProducts,
            'specialOffers' => $specialOffers,
        ]);
    }
}
