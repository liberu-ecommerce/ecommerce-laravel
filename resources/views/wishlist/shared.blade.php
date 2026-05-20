@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Shared Wishlist</h1>
    @if($wishlist->isEmpty())
        <p>This wishlist is empty.</p>
    @else
        <div class="row">
            @foreach($wishlist as $item)
                <div class="col-md-4 mb-3">
                    <div class="card">
                        <img src="/images/placeholder.png" alt="{{ $item->product->name }}" class="card-img-top">
                        <div class="card-body">
                            <h5 class="card-title">{{ $item->product->name }}</h5>
                            <p class="card-text">{{ $item->product->description }}</p>
                            <p class="card-text"><strong>Price:</strong> ${{ number_format($item->product->price, 2) }}</p>
                            <a href="{{ route('products.show', $item->product) }}" class="btn btn-primary">View Product</a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection