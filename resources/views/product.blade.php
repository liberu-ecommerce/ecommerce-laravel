{{-- This view renders the product details page, including information about the product, its aggregate ratings, and reviews. --}}
@extends('layouts.app')

@section('content')
<div class="container">
    <div class="product-details">
        <h2>{{ $product->name }}</h2>
        <p>{{ $product->description }}</p>
        <p><strong>Price:</strong> ${{ number_format($product->price, 2) }}</p>
        <!-- Displaying Aggregate Ratings -->
        <div class="aggregate-ratings">
            <h3>Customer Ratings</h3>
            <div class="row">
                <div class="col-md-6">
                    <h4>Overall Rating: {{ number_format($averageRatings['overall'], 1) }} / 5</h4>
                    <div class="progress">
                        <div class="progress-bar" role="progressbar" style="width: {{ $averageRatings['overall'] * 20 }}%" aria-valuenow="{{ $averageRatings['overall'] }}" aria-valuemin="0" aria-valuemax="5"></div>
                    </div>
                </div>
                <div class="col-md-6">
                    <p>Quality: {{ number_format($averageRatings['quality'], 1) }} / 5</p>
                    <p>Value: {{ number_format($averageRatings['value'], 1) }} / 5</p>
                    <p>Price: {{ number_format($averageRatings['price'], 1) }} / 5</p>
                </div>
            </div>
            <p>Total Reviews: {{ $reviews->count() }}</p>
        </div>
    </div>

    <!-- Review Form -->
    @auth
    <div class="review-form mt-4">
        <h3>Write a Review</h3>
        <form action="{{ route('reviews.store') }}" method="POST">
            @csrf
            <input type="hidden" name="product_id" value="{{ $product->id }}">
            <div class="form-group">
                <label for="overall_rating">Overall Rating</label>
                <input type="number" class="form-control" id="overall_rating" name="overall_rating" min="1" max="5" required>
            </div>
            <div class="form-group">
                <label for="quality_rating">Quality Rating</label>
                <input type="number" class="form-control" id="quality_rating" name="quality_rating" min="1" max="5" required>
            </div>
            <div class="form-group">
                <label for="value_rating">Value Rating</label>
                <input type="number" class="form-control" id="value_rating" name="value_rating" min="1" max="5" required>
            </div>
            <div class="form-group">
                <label for="price_rating">Price Rating</label>
                <input type="number" class="form-control" id="price_rating" name="price_rating" min="1" max="5" required>
            </div>
            <div class="form-group">
                <label for="comments">Review</label>
                <textarea class="form-control" id="comments" name="comments" rows="3" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Submit Review</button>
        </form>
    </div>
    @else
    <p>Please <a href="{{ route('login') }}">login</a> to leave a review.</p>
    @endauth

    <!-- Including the Reviews Section -->
    @include('reviews', ['reviews' => $reviews])
</div>
@endsection
