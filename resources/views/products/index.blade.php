@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 my-4">
        <div class="mb-4">
            <form method="GET" action="{{ route('products.index') }}" class="flex flex-wrap gap-2 items-end">
                <div>
                    <input type="text" name="keyword" value="{{ request('keyword') }}"
                           placeholder="Search products..."
                           class="border border-gray-300 rounded-md p-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <input type="number" name="filter[price_min]" value="{{ request('filter.price_min') }}"
                           placeholder="Min price"
                           class="border border-gray-300 rounded-md p-2 text-sm w-28 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <input type="number" name="filter[price_max]" value="{{ request('filter.price_max') }}"
                           placeholder="Max price"
                           class="border border-gray-300 rounded-md p-2 text-sm w-28 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <select name="sort" class="border border-gray-300 rounded-md p-2 text-sm">
                        <option value="">Sort by</option>
                        <option value="price" {{ request('sort') == 'price' ? 'selected' : '' }}>Price: Low to High</option>
                        <option value="-price" {{ request('sort') == '-price' ? 'selected' : '' }}>Price: High to Low</option>
                        <option value="name" {{ request('sort') == 'name' ? 'selected' : '' }}>Name: A to Z</option>
                        <option value="-name" {{ request('sort') == '-name' ? 'selected' : '' }}>Name: Z to A</option>
                        <option value="-created_at" {{ request('sort') == '-created_at' ? 'selected' : '' }}>Latest</option>
                    </select>
                </div>
                <button type="submit" class="text-white bg-blue-700 hover:bg-blue-800 font-medium rounded-lg text-sm px-4 py-2">
                    Search
                </button>
                @if(request()->hasAny(['keyword', 'filter', 'sort']))
                    <a href="{{ route('products.index') }}" class="text-gray-600 hover:text-gray-900 text-sm px-3 py-2 border border-gray-300 rounded-lg">
                        Clear
                    </a>
                @endif
            </form>
        </div>

        <!-- Products Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @if ($products->count() > 0)
                @foreach ($products as $product)
                    <div
                        class="col-span-1 max-w-sm bg-white border border-gray-200 rounded-lg shadow dark:bg-gray-800 dark:border-gray-700">
                        <a href="{{ route('products.show', ['product' => $product]) }}">
                            <img class="rounded-t-lg w-full h-48 object-cover" src="{{ $product->imageUrl ?? asset('images/placeholder.png') }}" alt="product image" />
                        </a>
                        <div class="px-5 pb-5">
                            <a href="{{ route('products.show', ['product' => $product]) }}">
                                <h5 class="text-xl font-semibold tracking-tight text-gray-900 dark:text-white mt-3">
                                    {{ $product->name }}</h5>
                            </a>
                            <p class="text-sm text-gray-500 mt-1 mb-3 line-clamp-2">{{ $product->short_description ?? $product->description }}</p>
                            <div class="flex items-center justify-between">
                                <span
                                    class="text-3xl font-bold text-gray-900 dark:text-white">${{ number_format($product->price, 2) }}</span>
                                <form action="{{ route('cart.add', $product) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit"
                                            class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800"
                                            {{ $product->inventory_count <= 0 ? 'disabled' : '' }}>
                                        @if($product->inventory_count <= 0)
                                            Out of Stock
                                        @else
                                            <svg class="-ms-2 me-2 h-5 w-5 inline" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4h1.5L8 16m0 0h8m-8 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4Zm8 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4Zm.75-3H7.5M11 7H6.312M17 4v6m-3-3h6" />
                                            </svg>
                                            Add to cart
                                        @endif
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="col-span-3">
                    <p class="text-gray-500">No products found{{ request('keyword') ? ' for "' . e(request('keyword')) . '"' : '' }}.</p>
                </div>
            @endif
        </div>
        <div class="mt-4 flex justify-center">
            {{ $products->links() }}
        </div>
    </div>
@endsection