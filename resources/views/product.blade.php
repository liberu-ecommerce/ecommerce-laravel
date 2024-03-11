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
            <p>Average Rating: {{ number_format($averageRating, 1) }} / 5</p>
            <p>Total Reviews: {{ $reviews->count() }}</p>
        </div>
    </div>

    <!-- Including the Reviews Section -->
    @include('reviews', ['reviews' => $reviews])
</div>
@endsection
