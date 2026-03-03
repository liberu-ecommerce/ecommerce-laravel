@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <nav class="text-sm text-gray-500 mb-4">
        <a href="{{ route('categories.index') }}" class="hover:text-blue-600">Categories</a>
        <span class="mx-2">/</span>
        <span class="text-gray-900">{{ $category->name }}</span>
    </nav>
    <div class="mb-6">
        <h1 class="text-2xl font-bold">{{ $category->name }}</h1>
        @if($category->description)
            <p class="text-gray-600 mt-2">{{ $category->description }}</p>
        @endif
    </div>
    <div class="mt-4">
        <a href="{{ route('categories.products', $category) }}" class="text-white bg-blue-700 hover:bg-blue-800 font-medium rounded-lg text-sm px-4 py-2">
            Browse Products
        </a>
    </div>
</div>
@endsection
