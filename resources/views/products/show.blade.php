@extends('layouts.app')

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
                    @if($product->inventory_count > 0)
                        <p class="text-success"><strong>In Stock</strong></p>
                    @else
                        <p class="text-danger"><strong>Out of Stock</strong></p>
                    @endif
                    @auth
                        @if(auth()->user()->wishlist()->where('product_id', $product->id)->exists())
                            <form action="{{ route('wishlist.remove', $product) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">Remove from Wishlist</button>
                            </form>
                        @else
                            <form action="{{ route('wishlist.add', $product) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-primary">Add to Wishlist</button>
                            </form>
                        @endif
                    @endauth
                    @if($product->inventory_count > 0)
                        <form action="{{ route('cart.add', $product) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-success mt-2">Add to Cart</button>
                        </form>
                    @endif
                    <form action="{{ route('products.addToCompare', $product) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-primary mt-2">Add to Compare</button>
                    </form>
                </div>
            </div>
            <a href="{{ route('products.index') }}" class="btn btn-primary mt-3">Back to Products</a>

            @if(count($recommendations) > 0)
                <div class="card mt-4">
                    <div class="card-header">Recommended Products</div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($recommendations as $recommendedProduct)
                                <div class="col-md-4 mb-3">
                                    <div class="card">
                                        <img src="/images/placeholder.png" alt="{{ $recommendedProduct->name }}" class="card-img-top">
                                        <div class="card-body">
                                            <h5 class="card-title">{{ $recommendedProduct->name }}</h5>
                                            <p class="card-text">${{ number_format($recommendedProduct->price, 2) }}</p>
                                            <a href="{{ route('products.show', $recommendedProduct->id) }}" class="btn btn-sm btn-primary">View Product</a>
                                            @if($recommendedProduct->inventory_count == 0)
                                                <p class="text-danger mt-2">Out of Stock</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

@if($product->downloadable->count() > 0 && auth()->user() && auth()->user()->hasPurchased($product))
    <a href="{{ route('download.generate-link', $product->id) }}" class="btn btn-success mt-3">Download</a>
@endif

<div class="card mt-4">
    <div class="card-header">Inventory Logs</div>
    <div class="card-body">
        <table class="table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Quantity Change</th>
                    <th>Reason</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($product->inventoryLogs as $log)
                    <tr>
                        <td>{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                        <td>{{ $log->quantity_change }}</td>
                        <td>{{ $log->reason }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<script type="application/ld+json">
{
    "@context": "https://schema.org/",
    "@type": "Product",
    "name": "{{ $product->name }}",
    "description": "{{ $product->description }}",
    "image": "{{ asset('/images/placeholder.png') }}",
    "sku": "{{ $product->id }}",
    "mpn": "{{ $product->id }}",
    "brand": {
        "@type": "Brand",
        "name": "{{ config('app.name') }}"
    },
    "offers": {
        "@type": "Offer",
        "url": "{{ route('products.show', $product->id) }}",
        "priceCurrency": "USD",
        "price": "{{ $product->price }}",
        "availability": "{{ $product->inventory_count > 0 ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock' }}",
        "seller": {
            "@type": "Organization",
            "name": "{{ config('app.name') }}"
        }
    }
}
</script>
@endsection

@section('meta')
    <meta name="description" content="{{ $product->meta_description ?? $product->short_description }}">
    <meta name="keywords" content="{{ $product->meta_keywords }}">
    <meta property="og:title" content="{{ $product->meta_title ?? $product->name }}">
    <meta property="og:description" content="{{ $product->meta_description ?? $product->short_description }}">
    <meta property="og:image" content="{{ asset('/images/placeholder.png') }}">
    <meta property="og:url" content="{{ route('products.show', $product->id) }}">
    <meta name="twitter:card" content="summary_large_image">
@endsection

