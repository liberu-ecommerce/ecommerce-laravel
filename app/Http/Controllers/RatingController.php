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
        $alreadyRated = Rating::where('user_id', Auth::id())
            ->where('product_id', $request->product_id)
            ->exists();

        if ($alreadyRated) {
            return response()->json(['message' => 'You have already rated this product'], 409);
        }

        $rating = new Rating();
        $rating->user_id = Auth::id();
        $rating->product_id = $request->product_id;
        $rating->rating = $request->overall_rating;
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

        // Composite score = mean of the available category averages (null when no ratings).
        $present = array_filter($averageRatings, fn ($v) => ! is_null($v));
        $overallAverage = count($present) ? round(array_sum($present) / count($present), 2) : null;

        return response()->json([
            'averageRatings' => $averageRatings,
            'overallAverage' => $overallAverage
        ]);
    }
}
