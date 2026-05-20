@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Product Comparison</h1>
    @if($products->count() > 0)
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Feature</th>
                        @foreach($products as $product)
                            <th>{{ $product->name }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Image</td>
                        @foreach($products as $product)
                            <td><img src="{{ $product->image_url ?? '/images/placeholder.png' }}" alt="{{ $product->name }}" class="img-thumbnail" style="max-width: 100px;"></td>
                        @endforeach
                    </tr>
                    <tr>
                        <td>Price</td>
                        @foreach($products as $product)
                            <td>${{ number_format($product->price, 2) }}</td>
                        @endforeach
                    </tr>
                    <tr>
                        <td>Category</td>
                        @foreach($products as $product)
                            <td>{{ $product->category }}</td>
                        @endforeach
                    </tr>
                    <tr>
                        <td>Description</td>
                        @foreach($products as $product)
                            <td>{{ $product->description }}</td>
                        @endforeach
                    </tr>
                    <tr>
                        <td>Inventory Count</td>
                        @foreach($products as $product)
                            <td>{{ $product->inventory_count }}</td>
                        @endforeach
                    </tr>
                    <tr>
                        <td>Actions</td>
                        @foreach($products as $product)
                            <td>
                                <a href="{{ route('products.show', $product->id) }}" class="btn btn-sm btn-primary">View</a>
                                <form action="{{ route('products.removeFromCompare', $product->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">Remove</button>
                                </form>
                            </td>
                        @endforeach
                    </tr>
                </tbody>
            </table>
        </div>
        <form action="{{ route('products.clearCompare') }}" method="POST">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-warning">Clear Comparison</button>
        </form>
    @else
        <p>No products to compare. <a href="{{ route('products.list') }}">Browse products</a> to add them to comparison.</p>
    @endif
</div>
@endsection