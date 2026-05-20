@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold mb-6">Search Results</h1>

    <form action="{{ route('products.search') }}" method="GET" class="mb-6">
        <div class="flex flex-wrap gap-2 items-end">
            <div>
                <input type="text" name="keyword" class="border border-gray-300 rounded-md p-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Search by keyword" value="{{ request('keyword') }}">
            </div>
            <div>
                <select name="category" class="border border-gray-300 rounded-md p-2 text-sm">
                    <option value="">All Categories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <input type="number" name="min_price" class="border border-gray-300 rounded-md p-2 text-sm w-28" placeholder="Min Price" value="{{ request('min_price') }}">
            </div>
            <div>
                <input type="number" name="max_price" class="border border-gray-300 rounded-md p-2 text-sm w-28" placeholder="Max Price" value="{{ request('max_price') }}">
            </div>
            <button type="submit" class="text-white bg-blue-700 hover:bg-blue-800 font-medium rounded-lg text-sm px-4 py-2">Search</button>
        </div>
    </form>

    @if(isset($keyword) && $keyword)
        <p class="text-gray-600 mb-4">Showing results for: <strong>{{ $keyword }}</strong></p>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @forelse($products as $product)
            <div class="bg-white border border-gray-200 rounded-lg shadow hover:shadow-md transition-shadow">
                <a href="{{ route('products.show', $product) }}">
                    <img src="{{ $product->imageUrl ?? asset('images/placeholder.png') }}"
                         class="rounded-t-lg w-full h-48 object-cover" alt="{{ $product->name }}">
                </a>
                <div class="px-5 pb-5 pt-3">
                    <a href="{{ route('products.show', $product) }}">
                        <h5 class="text-lg font-semibold text-gray-900 hover:text-blue-600 transition-colors mb-1">{{ $product->name }}</h5>
                    </a>
                    <p class="text-sm text-gray-500 mb-3 line-clamp-2">{{ $product->short_description ?? Str::limit($product->description, 100) }}</p>
                    <div class="flex items-center justify-between">
                        <span class="text-xl font-bold text-gray-900">${{ number_format($product->price, 2) }}</span>
                        <a href="{{ route('products.show', $product) }}" class="text-white bg-blue-700 hover:bg-blue-800 font-medium rounded-lg text-sm px-4 py-2">View Details</a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-3">
                <p class="text-gray-500">No products found matching your criteria.</p>
            </div>
        @endforelse
    </div>

    <div class="mt-6 flex justify-center">
        {{ $products->links() }}
    </div>
</div>
@endsection