@extends('layouts.app')

@section('title', 'Home')

@section('content')
    <!-- Hero Section -->
    <div class="bg-linear-to-b from-violet-600/10 via-transparent">
        <div class="max-w-340 mx-auto px-4 sm:px-6 lg:px-8 py-24 space-y-8">
            <div class="max-w-3xl text-center mx-auto">
                <h1 class="text-black block font-medium text-4xl sm:text-5xl md:text-6xl lg:text-7xl">
                    Welcome to {{config('app.name')}}
                </h1>
            </div>

            <div class="max-w-3xl text-center mx-auto">
                <p class="text-lg text-gray-700">
                    Explore our innovative and dynamic shopping platform with the best products at competitive prices.
                </p>
            </div>

            <div class="text-center">
                <a class="inline-flex justify-center items-center gap-x-3 text-center bg-linear-to-tl from-blue-600 to-violet-600 shadow-lg shadow-transparent hover:shadow-blue-700/50 border border-transparent text-white text-sm font-medium rounded-full focus:outline-none focus:shadow-blue-700/50 py-3 px-6"
                    href="{{ route('products.index') }}">
                    Shop Now
                    <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round">
                        <path d="m9 18 6-6-6-6" />
                    </svg>
                </a>
            </div>
        </div>
    </div>

    <!-- Featured Categories -->
    <div class="container mx-auto px-4 py-12">
        <h2 class="text-2xl font-bold mb-8 text-center">Shop by Category</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
            <a href="{{ route('products.index', ['category' => 'electronics']) }}" class="group">
                <div class="bg-white rounded-lg shadow-md overflow-hidden transition transform hover:scale-105">
                    <div class="h-40 bg-gray-200 flex items-center justify-center">
                        <svg class="h-16 w-16 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <div class="p-4 text-center">
                        <h3 class="font-medium">Electronics</h3>
                    </div>
                </div>
            </a>
            <a href="{{ route('products.index', ['category' => 'clothing']) }}" class="group">
                <div class="bg-white rounded-lg shadow-md overflow-hidden transition transform hover:scale-105">
                    <div class="h-40 bg-gray-200 flex items-center justify-center">
                        <svg class="h-16 w-16 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                        </svg>
                    </div>
                    <div class="p-4 text-center">
                        <h3 class="font-medium">Clothing</h3>
                    </div>
                </div>
            </a>
            <a href="{{ route('products.index', ['category' => 'home']) }}" class="group">
                <div class="bg-white rounded-lg shadow-md overflow-hidden transition transform hover:scale-105">
                    <div class="h-40 bg-gray-200 flex items-center justify-center">
                        <svg class="h-16 w-16 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 9h14l1 12H4L5 9z"></path>
                        </svg>
                    </div>
                    <div class="p-4 text-center">
                        <h3 class="font-medium">Home & Living</h3>
                    </div>
                </div>
            </a>
            <a href="{{ route('products.index', ['category' => 'books']) }}" class="group">
                <div class="bg-white rounded-lg shadow-md overflow-hidden transition transform hover:scale-105">
                    <div class="h-40 bg-gray-200 flex items-center justify-center">
                        <svg class="h-16 w-16 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                        </svg>
                    </div>
                    <div class="p-4 text-center">
                        <h3 class="font-medium">Books</h3>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- Latest Products -->
    <div class="container mx-auto px-4 py-12 bg-gray-50">
        <div class="flex justify-between items-center mb-8">
            <h2 class="text-2xl font-bold">Latest Products</h2>
            <a href="{{ route('products.index') }}" class="text-blue-600 hover:text-blue-800 font-medium">View All</a>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            @foreach($latestProducts as $product)
                <div class="bg-white rounded-lg shadow-md overflow-hidden transition transform hover:scale-105">
                    <a href="{{ route('products.show', $product->id) }}">
                        <img src="{{ $product->image_url ?? asset('images/placeholder.png') }}" alt="{{ $product->name }}" class="w-full h-48 object-cover">
                    </a>
                    <div class="p-4">
                        <a href="{{ route('products.show', $product->id) }}" class="text-lg font-medium text-gray-900 hover:text-blue-600">{{ $product->name }}</a>
                        <p class="text-gray-500 mt-1">{{ Str::limit($product->description, 60) }}</p>
                        <div class="mt-4 flex justify-between items-center">
                            <span class="text-gray-900 font-bold">${{ number_format($product->price, 2) }}</span>
                            <form action="{{ route('cart.add', $product) }}" method="POST">
                                @csrf
                                <button type="submit" class="bg-blue-600 text-white px-3 py-1 rounded-md hover:bg-blue-700">Add to Cart</button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

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
