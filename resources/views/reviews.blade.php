@extends('layouts.app')

@section('title', 'Product Reviews')

@section('content')
<div class="container mt-4">
    <h2>Product Reviews</h2>
    <div class="reviews-sort-filter mt-3 mb-4">
        <form action="{{ url()->current() }}" method="GET" class="form-inline">
            <label class="mr-sm-2" for="sort">Sort by:</label>
            <select name="sort" id="sort" class="form-control mr-sm-2">
                <option value="date_desc" {{ request('sort') == 'date_desc' ? 'selected' : '' }}>Date (Newest First)</option>
                <option value="date_asc" {{ request('sort') == 'date_asc' ? 'selected' : '' }}>Date (Oldest First)</option>
                <option value="rating_desc" {{ request('sort') == 'rating_desc' ? 'selected' : '' }}>Rating (High to Low)</option>
                <option value="rating_asc" {{ request('sort') == 'rating_asc' ? 'selected' : '' }}>Rating (Low to High)</option>
                <option value="relevance" {{ request('sort') == 'relevance' ? 'selected' : '' }}>Relevance</option>
            </select>
            <button type="submit" class="btn btn-primary">Apply</button>
        </form>
    </div>
    <div class="reviews-list">
        @if($reviews->isEmpty())
            <p>No reviews yet.</p>
        @else
            @foreach($reviews as $review)
                <div class="card mb-3">
                    <div class="card-body">
                        <h5 class="card-title">{{ $review->author }}</h5>
                        <p class="card-text">{{ $review->content }}</p>
                        <p class="card-text">Rating: {{ $review->rating }} / 5</p>
                        <p class="card-text">Date: {{ $review->created_at->toFormattedDateString() }}</p>
                    </div>
                </div>
            @endforeach
            {{ $reviews->links() }}
        @endif
    </div>
</div>
@endsection
