@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Product Reviews</h2>
    <div class="reviews-sort-filter">
        <form action="{{ url()->current() }}" method="GET">
            <div class="form-group">
                <label for="sort">Sort by:</label>
                <select name="sort" id="sort" class="form-control">
                    <option value="date_desc">Date (Newest First)</option>
                    <option value="date_asc">Date (Oldest First)</option>
                    <option value="rating_desc">Rating (High to Low)</option>
                    <option value="rating_asc">Rating (Low to High)</option>
                    <option value="relevance">Relevance</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Apply</button>
        </form>
    </div>
    @if($reviews->isEmpty())
        <p>No reviews yet.</p>
    @else
        @foreach($reviews as $review)
            <div class="review">
                <h4>{{ $review->author }}</h4>
                <p>{{ $review->content }}</p>
                <p>Rating: {{ $review->rating }} / 5</p>
                <p>Date: {{ $review->created_at->toFormattedDateString() }}</p>
            </div>
        @endforeach
        {{ $reviews->links() }}
    @endif
</div>
@endsection
