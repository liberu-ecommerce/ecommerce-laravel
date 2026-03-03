@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold mb-6">Product Categories</h1>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @if($categories->count() > 0)
            @foreach($categories as $category)
                <a href="{{ route('categories.products', $category) }}" class="block group">
                    <div class="card card-hover p-6 h-full">
                        <h5 class="text-xl font-semibold text-gray-900 group-hover:text-blue-600 transition-colors mb-2">{{ $category->name }}</h5>
                        <p class="text-gray-600 mb-3">{{ $category->description }}</p>
                        <p class="text-sm text-gray-500">{{ $category->products_count }} Products</p>
                    </div>
                </a>
            @endforeach
        @else
            <div class="col-span-3">
                <p class="text-gray-500">No categories available.</p>
            </div>
        @endif
    </div>
    <div class="mt-6 flex justify-center">
        {{ $categories->links() }}
    </div>
</div>
@endsection
