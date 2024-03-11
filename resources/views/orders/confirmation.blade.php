@extends('layouts.master')

@section('content')
<div class="order-confirmation">
    <h2>Order Confirmation</h2>
    <div class="order-summary">
        <h3>Order Summary</h3>
        <ul>
            @foreach($product_details as $product)
                <li>{{ $product['name'] }} - Quantity: {{ $product['quantity'] }} - Price: ${{ number_format($product['price'], 2) }}</li>
            @endforeach
        </ul>
        <p><strong>Total Amount:</strong> ${{ number_format($total_amount, 2) }}</p>
        <p><strong>Estimated Delivery:</strong> {{ $estimated_delivery }}</p>
    </div>
    <div class="order-actions">
        <a href="{{ route('shop.index') }}" class="btn btn-primary">Continue Shopping</a>
        <a href="{{ route('orders.history') }}" class="btn btn-secondary">View Order History</a>
    </div>
    @error('message')
        <div class="alert alert-danger">{{ $message }}</div>
    @enderror
</div>
@endsection
