@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ $tag->name }}</div>
                <div class="card-body">
                    <h3>Tag Page</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        @if($products->count() > 0)
            @foreach($products as $product)
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">{{ $product->name }}</h5>
                            <p class="card-text">{{ $product->description }}</p>
                            <p class="text-muted">Price: ${{ number_format($product->price, 2) }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        @else
            <div class="col-12">
                <p>No products available.</p>
            </div>
        @endif
    </div>
    <div class="row">
        <div class="col-12 d-flex justify-content-center">
            {{ $products->links() }}
        </div>
    </div>
</div>
@endsection 
