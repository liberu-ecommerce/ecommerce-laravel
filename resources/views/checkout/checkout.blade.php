@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Checkout</h1>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('checkout.process') }}" method="POST" id="checkout-form">
        @csrf
        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" class="form-control" id="email" name="email" required>
        </div>

        <div class="form-group">
            <label for="shipping_address">Shipping Address</label>
            <textarea class="form-control" id="shipping_address" name="shipping_address" required>{{ $shippingAddress }}</textarea>
            <small id="address-feedback" class="form-text text-muted"></small>
        </div>

        <div class="form-group">
            <label for="shipping_method">Shipping Method</label>
            <select class="form-control" id="shipping_method" name="shipping_method_id" required>
                @foreach($shippingMethods as $method)
                    <option value="{{ $method->id }}" data-base-rate="{{ $method->base_rate }}">
                        {{ $method->name }} - ${{ number_format($method->base_rate, 2) }} ({{ $method->estimated_delivery_time }})
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="payment_method">Payment Method</label>
            <select class="form-control" id="payment_method" name="payment_method" required>
                <option value="credit_card">Credit Card</option>
                <option value="paypal">PayPal</option>
            </select>
        </div>

        <div id="credit_card_fields" style="display: none;">
            <div class="form-group">
                <label for="card_number">Card Number</label>
                <input type="text" class="form-control" id="card_number" name="card_number">
            </div>
            <div class="form-group">
                <label for="expiry_date">Expiry Date</label>
                <input type="text" class="form-control" id="expiry_date" name="expiry_date" placeholder="MM/YY">
            </div>
            <div class="form-group">
                <label for="cvv">CVV</label>
                <input type="text" class="form-control" id="cvv" name="cvv">
            </div>
        </div>

        <h2>Order Summary</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Price</th>
                </tr>
            </thead>
            <tbody>
                @foreach($cart as $item)
                    <tr>
                        <td>{{ $item['name'] }}</td>
                        <td>{{ $item['quantity'] }}</td>
                        <td>${{ number_format($item['price'], 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="form-group">
            <label for="subtotal">Subtotal:</label>
            <span id="subtotal"></span>
        </div>

        <div class="form-group">
            <label for="shipping_cost">Shipping Cost:</label>
            <span id="shipping_cost"></span>
        </div>

        <div class="form-group">
            <label for="total">Total:</label>
            <span id="total"></span>
        </div>

        <button type="submit" class="btn btn-primary">Complete Checkout</button>
    </form>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('checkout-form');
        const shippingAddressInput = document.getElementById('shipping_address');
        const shippingMethodSelect = document.getElementById('shipping_method');
        const subtotalSpan = document.getElementById('subtotal');
        const shippingCostSpan = document.getElementById('shipping_cost');
        const totalSpan = document.getElementById('total');
        const addressFeedback = document.getElementById('address-feedback');

        function updateOrderSummary() {
            const cart = @json($cart);
            const subtotal = cart.reduce((sum, item) => sum + item.price * item.quantity, 0);
            const shippingMethod = shippingMethodSelect.options[shippingMethodSelect.selectedIndex];
            const shippingCost = parseFloat(shippingMethod.dataset.baseRate);
            const total = subtotal + shippingCost;

            subtotalSpan.textContent = `$${subtotal.toFixed(2)}`;
            shippingCostSpan.textContent = `$${shippingCost.toFixed(2)}`;
            totalSpan.textContent = `$${total.toFixed(2)}`;
        }

        function verifyAddress() {
            const address = shippingAddressInput.value;
            fetch(`/api/verify-address?address=${encodeURIComponent(address)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.isValid) {
                        addressFeedback.textContent = 'Address verified';
                        addressFeedback.className = 'form-text text-success';
                    } else {
                        addressFeedback.textContent = 'Invalid address. Please check and try again.';
                        addressFeedback.className = 'form-text text-danger';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    addressFeedback.textContent = 'Unable to verify address at this time.';
                    addressFeedback.className = 'form-text text-warning';
                });
        }

        shippingAddressInput.addEventListener('blur', verifyAddress);
        shippingMethodSelect.addEventListener('change', updateOrderSummary);

        document.getElementById('payment_method').addEventListener('change', function() {
            var creditCardFields = document.getElementById('credit_card_fields');
            if (this.value === 'credit_card') {
                creditCardFields.style.display = 'block';
            } else {
                creditCardFields.style.display = 'none';
            }
        });

        updateOrderSummary();
    });
</script>
@endpush
