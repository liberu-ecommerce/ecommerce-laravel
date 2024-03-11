&lt;div class="container mt-4">
    &lt;h2>Order Summary&lt;/h2>
    &lt;div class="row">
        &lt;div class="col-12 col-md-8">
            &lt;h4>Items&lt;/h4>
            &lt;ul class="list-group mb-3">
                @foreach($items as $item)
                    &lt;li class="list-group-item d-flex justify-content-between lh-condensed">
                        &lt;div>
                            &lt;h6 class="my-0">{{ $item->name }}&lt;/h6>
                            &lt;small class="text-muted">Quantity: {{ $item->quantity }}&lt;/small>
                        &lt;/div>
                        &lt;span class="text-muted">${{ number_format($item->price, 2) }}&lt;/span>
                    &lt;/li>
                @endforeach
            &lt;/ul>
            &lt;h4>Costs&lt;/h4>
            &lt;ul class="list-group mb-3">
                &lt;li class="list-group-item d-flex justify-content-between">
                    &lt;span>Total (USD)&lt;/span>
                    &lt;strong>${{ number_format($totalCost, 2) }}&lt;/strong>
                &lt;/li>
            &lt;/ul>
        &lt;/div>
        &lt;div class="col-12 col-md-4">
            &lt;h4>Shipping Information&lt;/h4>
            &lt;p>{{ $shippingInfo->name }}&lt;br>
                {{ $shippingInfo->address }}&lt;br>
                {{ $shippingInfo->city }}, {{ $shippingInfo->state }} {{ $shippingInfo->zip }}&lt;br>
                {{ $shippingInfo->country }}&lt;br>
                Phone: {{ $shippingInfo->phone }}
            &lt;/p>
            &lt;h4>Estimated Delivery Date&lt;/h4>
            &lt;p>{{ $estimatedDeliveryDate->format('F j, Y') }}&lt;/p>
        &lt;/div>
    &lt;/div>
    &lt;div class="row">
        &lt;div class="col-12">
            &lt;a href="{{ route('finalizePurchase') }}" class="btn btn-primary btn-lg btn-block">Confirm and Place Order&lt;/a>
        &lt;/div>
    &lt;/div>
&lt;/div>
