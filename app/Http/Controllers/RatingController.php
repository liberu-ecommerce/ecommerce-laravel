<?php

namespace App\Http\Controllers;

use App\Models\Rating;
use Illuminate\Http\Request;
use App\Http\Requests\RatingRequest;
use Illuminate\Support\Facades\Auth;

class RatingController extends Controller
{
    public function store(RatingRequest $request)
    {
        $rating = new Rating();
        $rating->user_id = Auth::id();
        $rating->product_id = $request->product_id;
        $rating->overall_rating = $request->overall_rating;
        $rating->quality_rating = $request->quality_rating;
        $rating->value_rating = $request->value_rating;
        $rating->price_rating = $request->price_rating;
        $rating->save();

        return response()->json(['message' => 'Rating submitted successfully', 'rating' => $rating], 201);
    }

    public function calculateAverageRating($productId)
    {
        $ratings = Rating::where('product_id', $productId)->get();

        $averageRatings = [
            'overall' => $ratings->avg('overall_rating'),
            'quality' => $ratings->avg('quality_rating'),
            'value' => $ratings->avg('value_rating'),
            'price' => $ratings->avg('price_rating'),
        ];

        // TODO: implement the method in the product model
        $overallAverage = 0;

        return response()->json([
            'averageRatings' => $averageRatings,
            'overallAverage' => $overallAverage
        ]);
    }
}
