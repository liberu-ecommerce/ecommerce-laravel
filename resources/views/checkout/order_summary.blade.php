@extends('layouts.app')

@section('content')
&lt;div class="container mt-4">
    &lt;h2>Order Summary&lt;/h2>
    &lt;div class="row">
        &lt;div class="col-12 col-md-8">
            &lt;h4>Items&lt;/h4>
            &lt;ul class="list-group mb-3">
                @foreach($order->items as $item)
                    &lt;li class="list-group-item d-flex justify-content-between lh-condensed">
                        &lt;div>
                            &lt;h6 class="my-0">{{ $item->product->name }}&lt;/h6>
                            &lt;small class="text-muted">Quantity: {{ $item->quantity }}&lt;/small>
                        &lt;/div>
                        &lt;span class="text-muted">${{ number_format($item->price, 2) }}&lt;/span>
                    &lt;/li>
                @endforeach
            &lt;/ul>
            &lt;h4>Costs&lt;/h4>
            &lt;ul class="list-group mb-3">
                &lt;li class="list-group-item d-flex justify-content-between">
                    &lt;span>Subtotal&lt;/span>
                    &lt;span>${{ number_format($order->items->sum('price'), 2) }}&lt;/span>
                &lt;/li>
                &lt;li class="list-group-item d-flex justify-content-between">
                    &lt;span>Shipping ({{ $order->shippingMethod->name }})&lt;/span>
                    &lt;span>${{ number_format($order->shippingMethod->price, 2) }}&lt;/span>
                &lt;/li>
                &lt;li class="list-group-item d-flex justify-content-between">
                    &lt;span>Total (USD)&lt;/span>
                    &lt;strong>${{ number_format($order->total_amount, 2) }}&lt;/strong>
                &lt;/li>
            &lt;/ul>
        &lt;/div>
        &lt;div class="col-12 col-md-4">
            &lt;h4>Shipping Information&lt;/h4>
            &lt;p>{{ $order->shipping_address }}&lt;/p>
            &lt;h4>Shipping Method&lt;/h4>
            &lt;p>{{ $order->shippingMethod->name }} - {{ $order->shippingMethod->estimated_delivery_time }}&lt;/p>
            &lt;h4>Payment Method&lt;/h4>
            &lt;p>{{ ucfirst($order->payment_method) }}&lt;/p>
            &lt;h4>Order Status&lt;/h4>
            &lt;p>{{ ucfirst($order->status) }}&lt;/p>
        &lt;/div>
    &lt;/div>
    &lt;div class="row mt-4">
        &lt;div class="col-12">
            &lt;a href="{{ route('home') }}" class="btn btn-primary">Back to Home&lt;/a>
            &lt;a href="{{ route('orders.track', $order->id) }}" class="btn btn-secondary">Track Order&lt;/a>
        &lt;/div>
    &lt;/div>
&lt;/div>
@endsection
