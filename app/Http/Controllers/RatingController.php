<?php

namespace App\Http\Controllers;

use App\Models\Rating;
use Illuminate\Http\Request;
use App\Http\Requests\RatingRequest;

class RatingController extends Controller
{
    public function store(RatingRequest $request)
    {
        $rating = new Rating();
        $rating->user_id = auth()->id();
        $rating->product_id = $request->product_id;
        $rating->rating = $request->rating;
        $rating->save();

        return response()->json(['message' => 'Rating submitted successfully', 'rating' => $rating], 201);
    }

    public function calculateAverageRating($productId)
    {
        $averageRating = Rating::calculateAverageRating($productId);

        return response()->json(['averageRating' => $averageRating]);
    }
}
