@extends('layouts.master')

@section('content')
<div class="container">
    <h1>Order History</h1>
    @if($orders->isEmpty())
        <p>No orders found.</p>
    @else
        <div class="orders-list">
            @foreach($orders as $order)
                <div class="order">
                    <h2>Order #{{ $order->id }}</h2>
                    <p><strong>Date:</strong> {{ $order->created_at->format('M d, Y') }}</p>
                    <p><strong>Total Amount:</strong> ${{ number_format($order->total_amount, 2) }}</p>
                    <p><strong>Payment Status:</strong> {{ $order->payment_status }}</p>
                    <p><strong>Shipping Status:</strong> {{ $order->shipping_status }}</p>
                    <p><strong>Order Status:</strong> {{ $order->order_status }}</p>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
