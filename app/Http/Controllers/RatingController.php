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
/**
 * RatingController handles HTTP requests related to ratings.
 * This includes storing new ratings and calculating average ratings for products.
 */
    /**
     * Handles the request to store a new rating.
     * 
     * @param RatingRequest $request The request object containing rating details.
     * @return \Illuminate\Http\JsonResponse A JSON response indicating success and the saved rating.
     */
    /**
     * Calculates and returns the average rating for a given product ID.
     * 
     * @param int $productId The ID of the product.
     * @return \Illuminate\Http\JsonResponse A JSON response containing the average rating.
     */
