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
                <option value="helpfulness" {{ request('sort') == 'helpfulness' ? 'selected' : '' }}>Most Helpful</option>
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
                        <h5 class="card-title">{{ $review->customer->name }}</h5>
                        @if($review->isVerifiedPurchase())
                            <span class="badge badge-success">Verified Purchase</span>
                        @endif
                        <p class="card-text">{{ $review->comments }}</p>
                        <p class="card-text">
                            Overall Rating: {{ $review->rating->overall_rating }} / 5<br>
                            Quality: {{ $review->rating->quality_rating }} / 5<br>
                            Value: {{ $review->rating->value_rating }} / 5<br>
                            Price: {{ $review->rating->price_rating }} / 5
                        </p>
                        <p class="card-text">Date: {{ $review->created_at->toFormattedDateString() }}</p>
                        <div class="helpfulness-voting">
                            <p>Was this review helpful?</p>
                            <button class="btn btn-sm btn-outline-primary vote-helpful" data-review-id="{{ $review->id }}">Yes ({{ $review->helpful_votes }})</button>
                            <button class="btn btn-sm btn-outline-secondary vote-unhelpful" data-review-id="{{ $review->id }}">No ({{ $review->unhelpful_votes }})</button>
                        </div>
                    </div>
                </div>
            @endforeach
            {{ $reviews->links() }}
        @endif
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        $('.vote-helpful, .vote-unhelpful').click(function() {
            var reviewId = $(this).data('review-id');
            var voteType = $(this).hasClass('vote-helpful') ? 'helpful' : 'unhelpful';

            $.ajax({
                url: '/reviews/' + reviewId + '/vote',
                method: 'POST',
                data: {
                    vote: voteType,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    // Update the vote count on the page
                    location.reload();
                },
                error: function(xhr) {
                    console.error('Error voting:', xhr.responseText);
                }
            });
        });
    });
</script>
@endpush

@endsection
