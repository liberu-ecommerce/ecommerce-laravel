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
        @include('components.products_section', ['products' => $featuredProducts])
    </div>
</div>

<div class="row">
    <div class="col-12">
        <h2>Latest Products</h2>
        @include('components.products_section', ['products' => $latestProducts])
    </div>
</div>

<div class="row">
    <div class="col-12">
        <h2>Special Offers</h2>
        <!-- Special offers content goes here -->
    </div>
</div>
@endsection
