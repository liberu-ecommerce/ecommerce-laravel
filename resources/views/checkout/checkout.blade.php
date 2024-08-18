@extends('layouts.app')

@section('content')
&lt;div class="container">
    &lt;h1>Checkout&lt;/h1>

    @if ($errors->any())
        &lt;div class="alert alert-danger">
            &lt;ul>
                @foreach ($errors->all() as $error)
                    &lt;li>{{ $error }}&lt;/li>
                @endforeach
            &lt;/ul>
        &lt;/div>
    @endif

    &lt;form action="{{ route('checkout.process') }}" method="POST">
        @csrf
        &lt;div class="form-group">
            &lt;label for="email">Email Address&lt;/label>
            &lt;input type="email" class="form-control" id="email" name="email" required>
        &lt;/div>

        &lt;div class="form-group">
            &lt;label for="shipping_address">Shipping Address&lt;/label>
            &lt;textarea class="form-control" id="shipping_address" name="shipping_address" required>&lt;/textarea>
        &lt;/div>

        &lt;div class="form-group">
            &lt;label for="shipping_method">Shipping Method&lt;/label>
            &lt;select class="form-control" id="shipping_method" name="shipping_method_id" required>
                @foreach($shippingMethods as $method)
                    &lt;option value="{{ $method->id }}">{{ $method->name }} - ${{ number_format($method->price, 2) }} ({{ $method->estimated_delivery_time }})&lt;/option>
                @endforeach
            &lt;/select>
        &lt;/div>

        &lt;div class="form-group">
            &lt;label for="payment_method">Payment Method&lt;/label>
            &lt;select class="form-control" id="payment_method" name="payment_method" required>
                &lt;option value="credit_card">Credit Card&lt;/option>
                &lt;option value="paypal">PayPal&lt;/option>
            &lt;/select>
        &lt;/div>

        &lt;div id="credit_card_fields" style="display: none;">
            &lt;div class="form-group">
                &lt;label for="card_number">Card Number&lt;/label>
                &lt;input type="text" class="form-control" id="card_number" name="card_number">
            &lt;/div>
            &lt;div class="form-group">
                &lt;label for="expiry_date">Expiry Date&lt;/label>
                &lt;input type="text" class="form-control" id="expiry_date" name="expiry_date" placeholder="MM/YY">
            &lt;/div>
            &lt;div class="form-group">
                &lt;label for="cvv">CVV&lt;/label>
                &lt;input type="text" class="form-control" id="cvv" name="cvv">
            &lt;/div>
        &lt;/div>

        &lt;h2>Order Summary&lt;/h2>
        &lt;table class="table">
            &lt;thead>
                &lt;tr>
                    &lt;th>Product&lt;/th>
                    &lt;th>Quantity&lt;/th>
                    &lt;th>Price&lt;/th>
                &lt;/tr>
            &lt;/thead>
            &lt;tbody>
                @foreach($cart as $item)
                    &lt;tr>
                        &lt;td>{{ $item['name'] }}&lt;/td>
                        &lt;td>{{ $item['quantity'] }}&lt;/td>
                        &lt;td>${{ number_format($item['price'], 2) }}&lt;/td>
                    &lt;/tr>
                @endforeach
            &lt;/tbody>
        &lt;/table>

        &lt;button type="submit" class="btn btn-primary">Complete Checkout&lt;/button>
    &lt;/form>
&lt;/div>
@endsection

@push('scripts')
&lt;script>
    document.getElementById('payment_method').addEventListener('change', function() {
        var creditCardFields = document.getElementById('credit_card_fields');
        if (this.value === 'credit_card') {
            creditCardFields.style.display = 'block';
        } else {
            creditCardFields.style.display = 'none';
        }
    });
&lt;/script>
@endpush
