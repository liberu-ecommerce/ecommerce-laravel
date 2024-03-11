@extends('layouts.app')
/**
 * This Blade template is used for displaying a single product's details.
 */

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ $product->name }}</div>
                <div class="card-body">
                    <img src="/images/placeholder.png" alt="{{ $product->name }}" class="img-fluid mb-3">
                    <p><strong>Description:</strong> {{ $product->description }}</p>
                    <p><strong>Price:</strong> ${{ number_format($product->price, 2) }}</p>
                    <p><strong>Category:</strong> {{ $product->category }}</p>
                    <p><strong>Inventory Count:</strong> {{ $product->inventory_count }}</p>
                </div>
            </div>
            <a href="{{ route('products.index') }}" class="btn btn-primary mt-3">Back to Products</a>
        </div>
    </div>
</div>
@endsection
