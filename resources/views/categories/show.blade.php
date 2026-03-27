@extends('layouts.app')

@section('content')
<div class="bg-gradient-to-br from-blue-50 via-white to-cyan-50 border-b border-gray-100">
    <div class="container mx-auto px-4 py-10 md:py-14">
        <nav class="text-sm text-gray-500 mb-5">
            <a href="{{ route('home') }}" class="hover:text-blue-600">Home</a>
            <span class="mx-2">/</span>
            <a href="{{ route('categories.index') }}" class="hover:text-blue-600">Categories</a>
            <span class="mx-2">/</span>
            <span class="text-gray-900">{{ $category->name }}</span>
        </nav>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-stretch">
            <div class="lg:col-span-2 bg-white/80 backdrop-blur rounded-2xl border border-gray-100 shadow-sm p-6 md:p-8">
                <p class="text-xs font-semibold tracking-widest uppercase text-blue-600">Category Spotlight</p>
                <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mt-3">{{ $category->name }}</h1>
                <p class="text-gray-600 mt-4 max-w-2xl">{{ $category->description ?: 'Explore curated picks, trending products, and trusted essentials in this category.' }}</p>

                <div class="flex flex-wrap gap-3 mt-6">
                    <a href="{{ route('categories.products', $category) }}" class="inline-flex items-center px-5 py-2.5 rounded-lg bg-blue-600 text-white font-medium hover:bg-blue-700 transition-colors">
                        View All Products
                    </a>
                    <a href="{{ route('products.index') }}" class="inline-flex items-center px-5 py-2.5 rounded-lg border border-gray-300 text-gray-700 font-medium hover:bg-gray-50 transition-colors">
                        View All Products
                    </a>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 flex flex-col justify-between">
                <div>
                    <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide">Category Snapshot</h2>
                    <p class="text-3xl font-bold text-gray-900 mt-3">{{ $productCount }}</p>
                    <p class="text-gray-600 mt-1">products available</p>
                </div>
                <div class="mt-6 text-sm text-gray-500">
                    <p>Updated for relevance and fresh arrivals.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container mx-auto px-4 py-8 md:py-10">
    @if($featuredProducts->isNotEmpty())
        <section class="mb-10">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl md:text-2xl font-semibold text-gray-900">Featured In {{ $category->name }}</h2>
                <a href="{{ route('categories.products', $category) }}" class="text-sm font-medium text-blue-600 hover:text-blue-700">View More</a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach($featuredProducts as $featuredProduct)
                    <article class="bg-white border border-gray-200 rounded-xl shadow-sm hover:shadow-md transition-shadow overflow-hidden">
                        <a href="{{ route('products.show', $featuredProduct) }}">
                            <img
                                class="w-full h-44 object-cover"
                                src="{{ $featuredProduct->image_url ?? asset('images/placeholder.png') }}"
                                alt="{{ $featuredProduct->name }}"
                            >
                        </a>
                        <div class="p-4">
                            <a href="{{ route('products.show', $featuredProduct) }}" class="font-semibold text-gray-900 hover:text-blue-600 line-clamp-1">
                                {{ $featuredProduct->name }}
                            </a>
                            <p class="text-sm text-gray-500 mt-2 line-clamp-2">{{ $featuredProduct->short_description ?? $featuredProduct->description }}</p>
                            <div class="mt-3 flex items-center justify-between">
                                <span class="text-lg font-bold text-gray-900">${{ number_format($featuredProduct->price, 2) }}</span>
                                <a href="{{ route('products.show', $featuredProduct) }}" class="text-sm font-medium text-blue-600 hover:text-blue-700">View</a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
    @else
        <section class="mb-10 border border-dashed border-gray-300 rounded-xl p-8 text-center bg-gray-50">
            <h2 class="text-lg font-semibold text-gray-900">No featured products yet</h2>
            <p class="text-gray-500 mt-2">This category is being updated. Check all products to discover what's available.</p>
        </section>
    @endif

    <section class="bg-white border border-gray-200 rounded-xl p-6 md:p-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h3 class="text-xl font-semibold text-gray-900">Ready to browse everything in {{ $category->name }}?</h3>
            <p class="text-gray-600 mt-2">Use sorting, filters, and pagination on the full products page.</p>
        </div>
        <a href="{{ route('categories.products', $category) }}" class="inline-flex justify-center items-center px-5 py-2.5 rounded-lg bg-blue-600 text-white font-medium hover:bg-blue-700 transition-colors">
            Open Products Listing
        </a>
    </section>
</div>
@endsection
