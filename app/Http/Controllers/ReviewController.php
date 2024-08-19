<?php

namespace App\Http\Controllers;

use App\Models\Review;
use Illuminate\Http\Request;
use App\Http\Requests\ReviewRequest;

class ReviewController extends Controller
{
    /**
     * Handles the request to store a new review.
     *
     * @param ReviewRequest $request The request object containing review details.
     * @return \Illuminate\Http\JsonResponse A JSON response indicating success and the saved review.
     */
    public function store(ReviewRequest $request)
    {
        $review = new Review();
        $review->user_id = Auth::id();
        $review->product_id = $request->product_id;
        $review->rating = $request->rating;
        $review->review = $request->review;
        $review->approved = false; // Reviews are not approved by default

        // Check if the user has purchased the product
        $hasOrderedProduct = Order::where('user_id', Auth::id())
            ->whereHas('orderItems', function ($query) use ($request) {
                $query->where('product_id', $request->product_id);
            })->exists();

        $review->is_verified_purchase = $hasOrderedProduct;

        $review->save();

        return response()->json(['message' => 'Review submitted successfully', 'review' => $review], 201);
    }

    public function approve($id)
    {
        $review = Review::find($id);
        if (!$review) {
            return response()->json(['message' => 'Review not found'], 404);
        }

        $review->approved = true;
        $review->save();
        return response()->json(['message' => 'Review approved successfully']);
    }

    public function show($productId)
    {
        $reviews = Review::where('product_id', $productId)
            ->where('approved', true)
            ->with('customer')
            ->get();
        return response()->json($reviews);
    }

    public function vote(Request $request, $id)
    {
        $review = Review::find($id);
        if (!$review) {
            return response()->json(['message' => 'Review not found'], 404);
        }

        if ($request->vote === 'helpful') {
            $review->helpful_votes++;
        } elseif ($request->vote === 'unhelpful') {
            $review->unhelpful_votes++;
        } else {
            return response()->json(['message' => 'Invalid vote type'], 400);
        }

        $review->save();
        return response()->json(['message' => 'Vote recorded successfully']);
    }
}
/**
 * ReviewController manages review-related actions such as storing, approving, and displaying reviews.
 */
    public function show($productId)
    {
        $reviews = Review::where('product_id', $productId)->where('approved', true)->get();
        return response()->json($reviews);
    }
}
    public function show($productId)
    {
        $reviews = Review::where('product_id', $productId)->where('approved', true)->get();
        return response()->json($reviews);
    }
}
