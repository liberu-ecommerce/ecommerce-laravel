@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Order #{{ $order->id }}</h1>
    <p>Date: {{ $order->created_at->format('Y-m-d H:i') }}</p>
    <p>Status: {{ ucfirst($order->status) }}</p>
    <p>Payment: {{ ucfirst($order->payment_status) }}</p>
    <p>Shipping: {{ ucfirst($order->shipping_status) }}</p>
    <p>Total: ${{ number_format($order->total_amount, 2) }}</p>

    <a href="{{ route('orders.index') }}" class="btn btn-secondary">Back to orders</a>
</div>
@endsection
