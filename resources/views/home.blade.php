@extends('layouts.app')

@section('title', 'Home')

@section('content')
    <!-- Enhanced Hero Section -->
    <div class="relative bg-gradient-to-br from-blue-50 via-white to-purple-50 overflow-hidden">
        <!-- Background Pattern -->
        <div class="absolute inset-0 bg-grid-pattern opacity-5"></div>

        <!-- Floating Elements -->
        <div class="absolute top-20 left-10 w-20 h-20 bg-blue-200 rounded-full opacity-20 animate-pulse-scale"></div>
        <div class="absolute top-40 right-20 w-16 h-16 bg-purple-200 rounded-full opacity-20 animate-pulse-scale" style="animation-delay: 1s;"></div>
        <div class="absolute bottom-20 left-1/4 w-12 h-12 bg-pink-200 rounded-full opacity-20 animate-pulse-scale" style="animation-delay: 2s;"></div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24 lg:py-32">
            <div class="text-center space-y-8 animate-fade-in-up">
                <div class="space-y-4">
                    <h1 class="heading-1 bg-gradient-to-r from-blue-600 via-purple-600 to-blue-800 bg-clip-text text-transparent">
                        Welcome to {{config('app.name')}}
                    </h1>
                    <p class="text-xl lg:text-2xl text-gray-600 max-w-3xl mx-auto leading-relaxed">
                        Discover amazing products with our innovative shopping platform. 
                        <span class="text-blue-600 font-semibold">Quality meets affordability</span> in every purchase.
                    </p>
                </div>

                <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
                    <a href="{{ route('products.index') }}" 
                       class="btn btn-primary btn-lg group">
                        <span>Shop Now</span>
                        <svg class="w-5 h-5 ml-2 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                        </svg>
                    </a>
                    <a href="#featured-categories" 
                       class="btn btn-secondary btn-lg">
                        Explore Categories
                    </a>
                </div>

                <!-- Trust Indicators -->
                <div class="flex flex-wrap justify-center items-center gap-8 pt-8 text-sm text-gray-500">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        <span>Free Shipping</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        <span>Secure Checkout</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-purple-500" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span>30-Day Returns</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Enhanced Featured Categories -->
    <section id="featured-categories" class="py-16 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="heading-2 mb-4">Shop by Category</h2>
                <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                    Discover our carefully curated collections designed to meet all your needs
                </p>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 lg:gap-8">
                <a href="{{ route('products.index', ['category' => 'electronics']) }}" 
                   class="group card card-hover">
                    <div class="relative h-48 bg-gradient-to-br from-blue-100 to-blue-200 flex items-center justify-center overflow-hidden">
                        <div class="absolute inset-0 bg-blue-500 opacity-0 group-hover:opacity-10 transition-opacity duration-300"></div>
                        <svg class="h-16 w-16 text-blue-600 group-hover:scale-110 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                        <div class="absolute top-3 right-3 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                            <span class="badge badge-primary">Hot</span>
                        </div>
                    </div>
                    <div class="p-6 text-center">
                        <h3 class="font-semibold text-lg text-gray-900 group-hover:text-blue-600 transition-colors">Electronics</h3>
                        <p class="text-sm text-gray-500 mt-1">Latest gadgets & tech</p>
                    </div>
                </a>
                <a href="{{ route('products.index', ['category' => 'clothing']) }}" 
                   class="group card card-hover">
                    <div class="relative h-48 bg-gradient-to-br from-pink-100 to-pink-200 flex items-center justify-center overflow-hidden">
                        <div class="absolute inset-0 bg-pink-500 opacity-0 group-hover:opacity-10 transition-opacity duration-300"></div>
                        <svg class="h-16 w-16 text-pink-600 group-hover:scale-110 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                        </svg>
                        <div class="absolute top-3 right-3 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                            <span class="badge badge-success">New</span>
                        </div>
                    </div>
                    <div class="p-6 text-center">
                        <h3 class="font-semibold text-lg text-gray-900 group-hover:text-pink-600 transition-colors">Clothing</h3>
                        <p class="text-sm text-gray-500 mt-1">Fashion & style</p>
                    </div>
                </a>

                <a href="{{ route('products.index', ['category' => 'home']) }}" 
                   class="group card card-hover">
                    <div class="relative h-48 bg-gradient-to-br from-green-100 to-green-200 flex items-center justify-center overflow-hidden">
                        <div class="absolute inset-0 bg-green-500 opacity-0 group-hover:opacity-10 transition-opacity duration-300"></div>
                        <svg class="h-16 w-16 text-green-600 group-hover:scale-110 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 9h14l1 12H4L5 9z"></path>
                        </svg>
                    </div>
                    <div class="p-6 text-center">
                        <h3 class="font-semibold text-lg text-gray-900 group-hover:text-green-600 transition-colors">Home & Living</h3>
                        <p class="text-sm text-gray-500 mt-1">Comfort & decor</p>
                    </div>
                </a>

                <a href="{{ route('products.index', ['category' => 'books']) }}" 
                   class="group card card-hover">
                    <div class="relative h-48 bg-gradient-to-br from-purple-100 to-purple-200 flex items-center justify-center overflow-hidden">
                        <div class="absolute inset-0 bg-purple-500 opacity-0 group-hover:opacity-10 transition-opacity duration-300"></div>
                        <svg class="h-16 w-16 text-purple-600 group-hover:scale-110 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                        </svg>
                        <div class="absolute top-3 right-3 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                            <span class="badge badge-warning">Sale</span>
                        </div>
                    </div>
                    <div class="p-6 text-center">
                        <h3 class="font-semibold text-lg text-gray-900 group-hover:text-purple-600 transition-colors">Books</h3>
                        <p class="text-sm text-gray-500 mt-1">Knowledge & stories</p>
                    </div>
                </a>
            </div>
        </div>
    </section>

    <!-- Enhanced Latest Products -->
    <section class="py-16 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col sm:flex-row justify-between items-center mb-12">
                <div>
                    <h2 class="heading-2 mb-2">Latest Products</h2>
                    <p class="text-gray-600">Discover our newest arrivals and trending items</p>
                </div>
                <a href="{{ route('products.index') }}" 
                   class="btn btn-secondary group mt-4 sm:mt-0">
                    <span>View All Products</span>
                    <svg class="w-4 h-4 ml-2 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                    </svg>
                </a>
            </div>

            <div class="product-grid">
                @foreach($latestProducts as $product)
                    <div class="product-card group">
                        <div class="relative overflow-hidden">
                            <a href="{{ route('products.show', $product->id) }}">
                                <img src="{{ $product->image_url ?? asset('images/placeholder.png') }}" 
                                     alt="{{ $product->name }}" 
                                     class="product-image">
                            </a>

                            <!-- Product badges -->
                            @if($product->isLowStock())
                                <div class="product-badge bg-orange-500">Low Stock</div>
                            @elseif($product->created_at->diffInDays() < 7)
                                <div class="product-badge bg-green-500">New</div>
                            @endif

                            <!-- Quick actions overlay -->
                            <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-20 transition-all duration-300 flex items-center justify-center opacity-0 group-hover:opacity-100">
                                <div class="flex space-x-2">
                                    <button class="btn btn-primary btn-sm" 
                                            data-tooltip="Quick View"
                                            onclick="quickView({{ $product->id }})">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </button>
                                    <button class="btn btn-secondary btn-sm" 
                                            data-tooltip="Add to Wishlist"
                                            onclick="addToWishlist({{ $product->id }})">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="p-6">
                            <div class="mb-2">
                                <a href="{{ route('products.show', $product->id) }}" 
                                   class="text-lg font-semibold text-gray-900 hover:text-blue-600 transition-colors line-clamp-2">
                                    {{ $product->name }}
                                </a>
                            </div>

                            <p class="text-gray-500 text-sm mb-3 line-clamp-2">
                                {{ Str::limit($product->description, 80) }}
                            </p>

                            <!-- Rating -->
                            <div class="flex items-center mb-3">
                                <div class="flex text-yellow-400">
                                    @for($i = 1; $i <= 5; $i++)
                                        <svg class="w-4 h-4 {{ $i <= $product->getAverageRating() ? 'fill-current' : 'text-gray-300' }}" viewBox="0 0 24 24">
                                            <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/>
                                        </svg>
                                    @endfor
                                </div>
                                <span class="text-sm text-gray-500 ml-2">({{ $product->getTotalReviews() }})</span>
                            </div>

                            <div class="flex justify-between items-center">
                                <div class="flex flex-col">
                                    <span class="text-xl font-bold text-gray-900">
                                        ${{ number_format($product->price, 2) }}
                                    </span>
                                    @if($product->inventory_count <= 5 && $product->inventory_count > 0)
                                        <span class="text-xs text-orange-600">Only {{ $product->inventory_count }} left</span>
                                    @endif
                                </div>

                                <form action="{{ route('cart.add', $product) }}" method="POST" class="add-to-cart-form">
                                    @csrf
                                    <button type="submit" 
                                            class="btn btn-primary btn-sm group"
                                            {{ $product->inventory_count <= 0 ? 'disabled' : '' }}>
                                        @if($product->inventory_count <= 0)
                                            <span>Out of Stock</span>
                                        @else
                                            <svg class="w-4 h-4 mr-1 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.5 5M7 13l2.5 5m0 0h8"></path>
                                            </svg>
                                            <span>Add to Cart</span>
                                        @endif
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Special Offers -->
    <div class="container mx-auto px-4 py-12">
        <h2 class="text-2xl font-bold mb-8">Special Offers</h2>
        <div class="bg-linear-to-r from-purple-500 to-blue-500 rounded-lg shadow-xl overflow-hidden">
            <div class="md:flex">
                <div class="md:w-1/2 p-8 md:p-12 text-white">
                    <h3 class="text-3xl font-bold mb-4">Summer Sale</h3>
                    <p class="text-lg mb-6">Get up to 50% off on selected items. Limited time offer!</p>
                    <a href="{{ route('products.index', ['sale' => 'true']) }}" class="inline-block bg-white text-blue-600 font-bold py-2 px-6 rounded-full hover:bg-gray-100 transition">Shop Now</a>
                </div>
                <div class="md:w-1/2 bg-white flex items-center justify-center p-8">
                    <img src="{{ asset('images/sale.jpg') }}" alt="Summer Sale" class="max-h-64">
                </div>
            </div>
        </div>
    </div>

    <!-- Testimonials -->
    <div class="container mx-auto px-4 py-12 bg-gray-50">
        <h2 class="text-2xl font-bold mb-8 text-center">What Our Customers Say</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="bg-white p-6 rounded-lg shadow-md">
                <div class="flex items-center mb-4">
                    <div class="h-12 w-12 rounded-full bg-blue-100 flex items-center justify-center text-blue-500 font-bold text-xl">J</div>
                    <div class="ml-4">
                        <h3 class="font-medium">John Doe</h3>
                        <div class="flex text-yellow-400">
                            <svg class="h-5 w-5 fill-current" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"></path></svg>
                            <svg class="h-5 w-5 fill-current" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"></path></svg>
                            <svg class="h-5 w-5 fill-current" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"></path></svg>
                            <svg class="h-5 w-5 fill-current" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"></path></svg>
                            <svg class="h-5 w-5 fill-current" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"></path></svg>
                        </div>
                    </div>
                </div>
                <p class="text-gray-600">"Great products and fast shipping. I'm very satisfied with my purchase and will definitely shop here again!"</p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-md">
                <div class="flex items-center mb-4">
                    <div class="h-12 w-12 rounded-full bg-green-100 flex items-center justify-center text-green-500 font-bold text-xl">S</div>
                    <div class="ml-4">
                        <h3 class="font-medium">Sarah Smith</h3>
                        <div class="flex text-yellow-400">
                            <svg class="h-5 w-5 fill-current" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"></path></svg>
                            <svg class="h-5 w-5 fill-current" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"></path></svg>
                            <svg class="h-5 w-5 fill-current" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"></path></svg>
                            <svg class="h-5 w-5 fill-current" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"></path></svg>
                            <svg class="h-5 w-5 fill-current" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"></path></svg>
                        </div>
                    </div>
                </div>
                <p class="text-gray-600">"The customer service is exceptional. They helped me resolve an issue with my order quickly and efficiently."</p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-md">
                <div class="flex items-center mb-4">
                    <div class="h-12 w-12 rounded-full bg-red-100 flex items-center justify-center text-red-500 font-bold text-xl">M</div>
                    <div class="ml-4">
                        <h3 class="font-medium">Michael Johnson</h3>
                        <div class="flex text-yellow-400">
                            <svg class="h-5 w-5 fill-current" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"></path></svg>
                            <svg class="h-5 w-5 fill-current" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"></path></svg>
                            <svg class="h-5 w-5 fill-current" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"></path></svg>
                            <svg class="h-5 w-5 fill-current" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"></path></svg>
                            <svg class="h-5 w-5 fill-current" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"></path></svg>
                        </div>
                    </div>
                </div>
                <p class="text-gray-600">"The quality of the products exceeded my expectations. Will definitely recommend to friends and family!"</p>
            </div>
        </div>
    </div>
@endsection
