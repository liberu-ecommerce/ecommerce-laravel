@extends('layouts.app')

@section('title', 'Home')

@section('content')
<div class="row">
    <div class="col-12">
        <h1>Welcome to Liberu Ecommerce</h1>
        <p>Explore our innovative and dynamic shopping platform.</p>
    </div>
</div>
<div class="row">
    <div class="col-12">
        <h2>Featured Products</h2>
        <div class="row">
            @foreach($products as $product)
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <div class="card-inner">
                            <div class="card-front">
                                <img src="{{ $product->image_url }}" class="card-img-top" alt="{{ $product->name }}">
                                <div class="card-body">
                                    <h5 class="card-title">{{ $product->name }}</h5>
                                    <p class="card-text">{{ $product->description }}</p>
                                    <a href="#" class="btn btn-primary">View Product</a>
                                </div>
                            </div>
                            <div class="card-back">
                                <div class="card-body">
                                    <h5 class="card-title">{{ $product->name }}</h5>
                                    <p class="card-text">{{ $product->description }}</p>
                                    <p><strong>Price:</strong> ${{ $product->price }}</p>
                                    <a href="#" class="btn btn-success">Add to Cart</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
<div class="row">
    <div class="col-12">
        <h2>Special Offers</h2>
        <!-- Special offers content goes here -->
    </div>
</div>
@endsection
