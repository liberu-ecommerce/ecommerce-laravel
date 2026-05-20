{{-- This view renders the product details page, including information about the product, its aggregate ratings, and reviews. --}}
@extends('layouts.app')

@section('title', $product->name)
@section('meta_description', Str::limit($product->description, 160))

@section('content')
<div class="container mx-auto px-4 py-8">
    <nav class="flex mb-6" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-3">
            <li class="inline-flex items-center">
                <a href="{{ route('home') }}" class="text-gray-700 hover:text-blue-600">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path></svg>
                    Home
                </a>
            </li>
            <li>
                <div class="flex items-center">
                    <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                    <a href="{{ route('products.index') }}" class="ml-1 text-gray-700 hover:text-blue-600 md:ml-2">Products</a>
                </div>
            </li>
            <li aria-current="page">
                <div class="flex items-center">
                    <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                    <span class="ml-1 text-gray-500 md:ml-2">{{ $product->name }}</span>
                </div>
            </li>
        </ol>
    </nav>

    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="md:flex">
            <div class="md:w-1/2 p-4">
                <img src="{{ $product->image_url ?? asset('images/placeholder.png') }}" alt="{{ $product->name }}" class="w-full h-auto rounded-lg">
                <div class="grid grid-cols-4 gap-2 mt-4">
                    <img src="{{ $product->image_url ?? asset('images/placeholder.png') }}" alt="{{ $product->name }}" class="w-full h-20 object-cover rounded cursor-pointer border-2 border-blue-500">
                    <img src="{{ asset('images/placeholder.png') }}" alt="{{ $product->name }}" class="w-full h-20 object-cover rounded cursor-pointer">
                    <img src="{{ asset('images/placeholder.png') }}" alt="{{ $product->name }}" class="w-full h-20 object-cover rounded cursor-pointer">
                    <img src="{{ asset('images/placeholder.png') }}" alt="{{ $product->name }}" class="w-full h-20 object-cover rounded cursor-pointer">
                </div>
            </div>
            <div class="md:w-1/2 p-6">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ $product->name }}</h1>

                <div class="flex items-center mb-4">
                    <div class="flex text-yellow-400">
                        @for ($i = 1; $i <= 5; $i++)
                            @if ($i <= $averageRatings['overall'])
                                <svg class="h-5 w-5 fill-current" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"></path></svg>
                            @elseif ($i - 0.5 <= $averageRatings['overall'])
                                <svg class="h-5 w-5 fill-current" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"></path></svg>
                            @else
                                <svg class="h-5 w-5 fill-current text-gray-300" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"></path></svg>
                            @endif
                        @endfor
                    </div>
                    <span class="ml-2 text-gray-600">{{ number_format($averageRatings['overall'], 1) }} ({{ $reviews->count() }} reviews)</span>
                </div>

                <div class="text-2xl font-bold text-gray-900 mb-4">${{ number_format($product->price, 2) }}</div>

                <div class="mb-6">
                    <h2 class="text-lg font-semibold mb-2">Description</h2>
                    <p class="text-gray-600">{{ $product->description }}</p>
                </div>

                <div class="mb-6">
                    <h2 class="text-lg font-semibold mb-2">Availability</h2>
                    @if($product->inventory_count > 0)
                        <p class="text-green-600">In Stock ({{ $product->inventory_count }} available)</p>
                    @else
                        <p class="text-re-600">Out of Stock</p>
                    @endif
                </div>

                @if($product->inventory_count > 0)
                    <form action="{{ route('cart.add', $product) }}" method="POST" class="mb-6">
                        @csrf
                        <div class="flex items-center mb-4">
                            <label for="quantity" class="mr-4 font-medium">Quantity:</label>
                            <div class="custom-number-input h-10 w-32">
                                <div class="flex flex-row h-10 w-full rounded-lg relative bg-transparent mt-1">
                                    <button type="button" class="bg-gray-200 text-gray-600 hover:text-gray-700 hover:bg-gray-300 h-full w-20 rounded-l cursor-pointer outline-none" onclick="this.parentNode.querySelector('input[type=number]').stepDown()">
                                        <span class="m-auto text-2xl font-thin">−</span>
                                    </button>
                                    <input type="number" name="quantity" id="quantity" class="outline-none focus:outline-none text-center w-full bg-gray-100 font-semibold text-md hover:text-black focus:text-black md:text-base cursor-default flex items-center text-gray-700" min="1" max="{{ $product->inventory_count }}" value="1">
                                    <button type="button" class="bg-gray-200 text-gray-600 hover:text-gray-700 hover:bg-gray-300 h-full w-20 rounded-r cursor-pointer" onclick="this.parentNode.querySelector('input[type=number]').stepUp()">
                                        <span class="m-auto text-2xl font-thin">+</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg transition">
                            Add to Cart
                        </button>
                    </form>

                    <div class="flex space-x-4">
                        @auth
                            @if(auth()->user()->wishlist()->where('product_id', $product->id)->exists())
                                <form action="{{ route('wishlist.remove', $product) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="flex items-center text-gray-600 hover:text-red-600">
                                        <svg class="h-5 w-5 mr-1 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"></path>
                                        </svg>
                                        Remove from Wishlist
                                    </button>
                                </form>
                            @else
                                <form action="{{ route('wishlist.add', $product) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="flex items-center text-gray-600 hover:text-red-600">
                                        <svg class="h-5 w-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4 4 0 000 6.364L12 20.364l7.682-7.682a4 4 0 00-6.364-6.364L12 7.636l-1.318-1.318a4 4 0 00-6.364 0z"></path>
                                        </svg>
                                        Add to Wishlist
                                    </button>
                                </form>
                            @endif
                        @endauth
                        <button class="flex items-center text-gray-600 hover:text-blue-600">
                            <svg class="h-5 w-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"></path>
                            </svg>
                            Share
                        </button>
                    </div>
                @else
                    <button disabled class="w-full bg-gray-400 text-white font-bold py-3 px-4 rounded-lg cursor-not-allowed">
                        Out of Stock
                    </button>
                    <div class="mt-4">
                        <a href="#" class="text-blue-600 hover:text-blue-800">
                            Notify me when back in stock
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Product Details Tabs -->
    <div class="mt-8">
        <div class="border-b border-gray-200">
            <nav class="-mb-px flex space-x-8">
                <a href="#description" class="border-blue-500 text-blue-600 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    Description
                </a>
                <a href="#specifications" class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    Specifications
                </a>
                <a href="#reviews" class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    Reviews ({{ $reviews->count() }})
                </a>
            </nav>
        </div>

        <!-- Description Tab Content -->
        <div id="description" class="py-6">
            <h2 class="text-xl font-bold mb-4">Product Description</h2>
            <p class="text-gray-600">{{ $product->description }}</p>
            <!-- Add more detailed description here -->
        </div>
    </div>

    <!-- Customer Ratings -->
    <div class="mt-8 bg-white rounded-lg shadow-md p-6">
        <h2 class="text-xl font-bold mb-6">Customer Ratings</h2>
        <div class="md:flex">
            <div class="md:w-1/3 mb-6 md:mb-0">
                <div class="flex items-center mb-2">
                    <div class="text-5xl font-bold text-gray-900">{{ number_format($averageRatings['overall'], 1) }}</div>
                    <div class="ml-4">
                        <div class="flex text-yellow-400">
                            @for ($i = 1; $i <= 5; $i++)
                                @if ($i <= $averageRatings['overall'])
                                    <svg class="h-5 w-5 fill-current" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"></path></svg>
                                @elseif ($i - 0.5 <= $averageRatings['overall'])
                                    <svg class="h-5 w-5 fill-current" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"></path></svg>
                                @else
                                    <svg class="h-5 w-5 fill-current text-gray-300" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"></path></svg>
                                @endif
                            @endfor
                        </div>
                        <div class="text-sm text-gray-500">{{ $reviews->count() }} reviews</div>
                    </div>
                </div>
            </div>
            <div class="md:w-2/3">
                <div class="space-y-3">
                    <div class="flex items-center">
                        <span class="text-sm font-medium w-20">Quality</span>
                        <div class="w-full bg-gray-200 rounded-full h-2.5 ml-2">
                            <div class="bg-blue-600 h-2.5 rounded-full" style="width: {{ $averageRatings['quality'] * 20 }}%"></div>
                        </div>
                        <span class="ml-2 text-sm font-medium text-gray-700">{{ number_format($averageRatings['quality'], 1) }}</span>
                    </div>
                    <div class="flex items-center">
                        <span class="text-sm font-medium w-20">Value</span>
                        <div class="w-full bg-gray-200 rounded-full h-2.5 ml-2">
                            <div class="bg-blue-600 h-2.5 rounded-full" style="width: {{ $averageRatings['value'] * 20 }}%"></div>
                        </div>
                        <span class="ml-2 text-sm font-medium text-gray-700">{{ number_format($averageRatings['value'], 1) }}</span>
                    </div>
                    <div class="flex items-center">
                        <span class="text-sm font-medium w-20">Price</span>
                        <div class="w-full bg-gray-200 rounded-full h-2.5 ml-2">
                            <div class="bg-blue-600 h-2.5 rounded-full" style="width: {{ $averageRatings['price'] * 20 }}%"></div>
                        </div>
                        <span class="ml-2 text-sm font-medium text-gray-700">{{ number_format($averageRatings['price'], 1) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Review Form -->
    <div id="reviews" class="mt-8 bg-white rounded-lg shadow-md p-6">
        <h2 class="text-xl font-bold mb-6">Customer Reviews</h2>

        @auth
        <div class="mb-8 border-b pb-8">
            <h3 class="text-lg font-semibold mb-4">Write a Review</h3>
            <form action="{{ route('reviews.store') }}" method="POST">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Overall Rating</label>
                    <div class="flex">
                        @for ($i = 1; $i <= 5; $i++)
                            <label class="mr-2 cursor-pointer">
                                <input type="radio" name="overall_rating" value="{{ $i }}" class="hidden peer" required>
                                <svg class="h-8 w-8 text-gray-300 peer-checked:text-yellow-400" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"></path>
                                </svg>
                            </label>
                        @endfor
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <div>
                        <label for="quality_rating" class="block text-gray-700 text-sm font-bold mb-2">Quality Rating</label>
                        <select id="quality_rating" name="quality_rating" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                            <option value="">Select Rating</option>
                            @for ($i = 1; $i <= 5; $i++)
                                <option value="{{ $i }}">{{ $i }} Star{{ $i > 1 ? 's' : '' }}</option>
                            @endfor
                        </select>
                    </div>
                    <div>
                        <label for="value_rating" class="block text-gray-700 text-sm font-bold mb-2">Value Rating</label>
                        <select id="value_rating" name="value_rating" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                            <option value="">Select Rating</option>
                            @for ($i = 1; $i <= 5; $i++)
                                <option value="{{ $i }}">{{ $i }} Star{{ $i > 1 ? 's' : '' }}</option>
                            @endfor
                        </select>
                    </div>
                    <div>
                        <label for="price_rating" class="block text-gray-700 text-sm font-bold mb-2">Price Rating</label>
                        <select id="price_rating" name="price_rating" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                            <option value="">Select Rating</option>
                            @for ($i = 1; $i <= 5; $i++)
                                <option value="{{ $i }}">{{ $i }} Star{{ $i > 1 ? 's' : '' }}</option>
                            @endfor
                        </select>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="comments" class="block text-gray-700 text-sm font-bold mb-2">Review</label>
                    <textarea id="comments" name="comments" rows="4" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Share your experience with this product..." require></textarea>
                </div>

                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline transition">
                    Submit Review
                </button>
            </form>
        </div>
        @else
        <div class="bg-gray-50 p-4 rounded-lg mb-8">
            <p class="text-gray-700">Please <a href="{{ route('login') }}" class="text-blue-600 hover:text-blue-800 font-medium">login</a> to leave a review.</p>
        </div>
        @endauth

        <!-- Reviews List -->
        <div class="space-y-6">
            @if($reviews->isEmpty())
                <p class="text-gray-500">No reviews yet. Be the first to review this product!</p>
            @else
                @foreach($reviews as $review)
                    <div class="border-b pb-6 last:border-b-0">
                        <div class="flex justify-between items-start">
                            <div>
                                <div class="flex items-center">
                                    <div class="flex text-yellow-400">
                                        @for ($i = 1; $i <= 5; $i++)
                                            @if ($i <= $review->rating->overall_rating)
                                                <svg class="h-5 w-5 fill-current" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"></path></svg>
                                            @else
                                                <svg class="h-5 w-5 fill-current text-gray-300" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"></path></svg>
                                            @endif
                                        @endfor
                                    </div>
                                    <h3 class="ml-2 text-lg font-semibold text-gray-900">{{ $review->customer->name }}</h3>
                                    @if($review->isVerifiedPurchase())
                                        <span class="ml-2 bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded">Verified Purchase</span>
                                    @endif
                                </div>
                                <p class="text-sm text-gray-500 mt-1">{{ $review->created_at->toFormattedDateString() }}</p>
                            </div>
                        </div>
                        <p class="mt-4 text-gray-700">{{ $review->comments }}</p>
                        <div class="mt-4 text-sm text-gray-500">
                            <span>Quality: {{ $review->rating->quality_rating }}/5</span> •
                            <span>Value: {{ $review->rating->value_rating }}/5</span> •
                            <span>Price: {{ $review->rating->price_rating }}/5</span>
                        </div>
                        <div class="mt-4 flex items-center">
                            <span class="text-sm text-gray-500 mr-4">Was this review helpful?</span>
                            <button class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-1 px-3 rounded-full text-xs mr-2 vote-helpful" data-review-id="{{ $review->id }}">
                                Yes ({{ $review->helpful_votes }})
                            </button>
                            <button class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-1 px-3 rounded-full text-xs vote-unhelpful" data-review-id="{{ $review->id }}">
                                No ({{ $review->unhelpful_votes }})
                            </button>
                        </div>
                    </div>
                @endforeach

                <div class="mt-6">
                    {{ $reviews->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Related Products -->
    <div class="mt-12">
        <h2 class="text-2xl font-bold mb-6">You May Also Like</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6">
            @for ($i = 0; $i < 4; $i++)
                <div class="bg-white rounded-lg shadow-md overflow-hidden transition transform hover:scale-105">
                    <a href="#">
                        <img src="{{ asset('images/placeholder.png') }}" alt="Related Product" class="w-full h-48 object-cover">
                    </a>
                    <div class="p-4">
                        <a href="#" class="text-lg font-medium text-gray-900 hover:text-blue-600">Related Product {{ $i + 1 }}</a>
                        <p class="text-gray-500 mt-1">Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
                        <div class="mt-4 flex justify-between items-center">
                            <span class="text-gray-900 font-bold">$99.99</span>
                            <button class="bg-blue-600 text-white px-3 py-1 rounded-md hover:bg-blue-700">Add to Cart</button>
                        </div>
                    </div>
                </div>
            @endfor
        </div>
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