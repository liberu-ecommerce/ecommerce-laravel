@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-6">
        <nav class="text-sm text-gray-500 mb-2">
            <a href="{{ route('categories.index') }}" class="hover:text-blue-600">Categories</a>
            <span class="mx-2">/</span>
            <span class="text-gray-900">{{ $category->name }}</span>
        </nav>
        <h1 class="text-2xl font-bold">{{ $category->name }}</h1>
        @if($category->description)
            <p class="text-gray-600 mt-1">{{ $category->description }}</p>
        @endif
    </div>

    <div class="flex justify-between items-center mb-4">
        <p class="text-gray-500">{{ $products->total() }} products</p>
        <form method="GET" action="{{ route('categories.products', $category) }}">
            <select name="sort" class="border border-gray-300 rounded-md p-2 text-sm" onchange="this.form.submit()">
                <option value="">Sort by</option>
                <option value="price" {{ request('sort') == 'price' ? 'selected' : '' }}>Price: Low to High</option>
                <option value="-price" {{ request('sort') == '-price' ? 'selected' : '' }}>Price: High to Low</option>
                <option value="name" {{ request('sort') == 'name' ? 'selected' : '' }}>Name: A to Z</option>
                <option value="-name" {{ request('sort') == '-name' ? 'selected' : '' }}>Name: Z to A</option>
                <option value="-created_at" {{ request('sort') == '-created_at' ? 'selected' : '' }}>Latest</option>
            </select>
        </form>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @if($products->count() > 0)
            @foreach($products as $product)
                <div class="bg-white border border-gray-200 rounded-lg shadow hover:shadow-md transition-shadow">
                    <a href="{{ route('products.show', $product) }}">
                        <img class="rounded-t-lg w-full h-48 object-cover"
                             src="{{ $product->imageUrl ?? asset('images/placeholder.png') }}"
                             alt="{{ $product->name }}" />
                    </a>
                    <div class="px-5 pb-5 pt-3">
                        <a href="{{ route('products.show', $product) }}">
                            <h5 class="text-lg font-semibold text-gray-900 hover:text-blue-600 transition-colors line-clamp-2 mb-2">
                                {{ $product->name }}
                            </h5>
                        </a>
                        <p class="text-sm text-gray-500 mb-3 line-clamp-2">{{ $product->short_description ?? $product->description }}</p>
                        <div class="flex items-center justify-between">
                            <span class="text-2xl font-bold text-gray-900">${{ number_format($product->price, 2) }}</span>
                            <form action="{{ route('cart.add', $product) }}" method="POST">
                                @csrf
                                <button type="submit"
                                        class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center"
                                        {{ $product->inventory_count <= 0 ? 'disabled' : '' }}>
                                    {{ $product->inventory_count <= 0 ? 'Out of Stock' : 'Add to Cart' }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        @else
            <div class="col-span-3">
                <p class="text-gray-500">No products available in this category.</p>
            </div>
        @endif
    </div>

    <div class="mt-6 flex justify-center">
        {{ $products->links() }}
    </div>
</div>
@endsection
