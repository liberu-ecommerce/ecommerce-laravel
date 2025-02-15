@extends('layouts.app')

@section('content')
<div class="container p-8">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h2 class="text-xl font-semibold tracking-tight text-gray-900 dark:text-white">
                        {{ $product->name }}
                    </h2>
                </div>
                <div class="card-body">
                    <img src="{{ $product->imageUrl }}" alt="{{ $product->name }}" class="img-fluid mb-3">
                    <h4>Description:</h4>
                    <p>{{ $product->long_description }}</p>

                    <br>
                    @if($product->isFree())
                        <p><strong>Price:</strong> Free</p>
                        <a href="{{ route('download.generate-link', $product->id) }}" class="btn btn-success mt-2">Download Now</a>
                    @elseif($product->isDonationBased())
                        <form action="{{ route('cart.add', $product) }}" method="POST" class="d-inline">
                            @csrf
                            <div class="form-group">
                                <label for="donation_amount">Support this product (Suggested: ${{ number_format($product->suggested_price, 2) }})</label>
                                <input type="number" 
                                       name="price" 
                                       id="donation_amount" 
                                       class="form-control" 
                                       value="{{ $product->suggested_price }}"
                                       min="{{ $product->minimum_price }}"
                                       step="0.01">
                            </div>
                            <button type="submit" class="btn btn-success mt-2">Support & Download</button>
                        </form>
                        @if($product->minimum_price <= 0)
                            <a href="{{ route('download.generate-link', $product->id) }}" class="btn btn-link">Download without donating</a>
                        @endif
                    @else
                        <p><strong>Price:</strong> ${{ number_format($product->price, 2) }}</p>
                        @if($product->inventory_count > 0)
                            <form action="{{ route('cart.add', $product) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-success mt-2">Add to Cart</button>
                            </form>
                        @endif
                    @endif
                    <p><strong>Category:</strong> {{ $product->category->name ?? "" }}</p>
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
                    {{-- <form action="{{ route('products.addToCompare', $product) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-primary mt-2">Add to Compare</button>
                    </form> --}}
                </div>
            </div>
            <a href="{{ route('products.index') }}" class="btn btn-primary mt-3">Back to Products</a>

            @if(isset($recommendations) && count($recommendations) > 0)
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
@if(isset($product->downloadable) && $product->downloadable->count() > 0 && auth()->user() && auth()->user()->hasPurchased($product))
    <a href="{{ route('download.generate-link', $product->id) }}" class="btn btn-success mt-3">Download</a>
@endif

@isset($product->inventoryLogs)
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
@endisset

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