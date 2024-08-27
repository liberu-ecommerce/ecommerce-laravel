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
                <option value="stripe">Credit Card (Stripe)</option>
                <option value="paypal">PayPal</option>
                <!-- Add more payment options here as they become available -->
            </select>
        </div>

        <div id="stripe_fields" style="display: none;">
            <div id="card-element">
                <!-- A Stripe Element will be inserted here. -->
            </div>
            <!-- Used to display form errors. -->
            <div id="card-errors" role="alert"></div>
        </div>

        <div id="paypal_fields" style="display: none;">
            <!-- PayPal button will be rendered here -->
            <div id="paypal-button-container"></div>
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

        <button type="submit" class="btn btn-primary" id="submit-button">Complete Checkout</button>
    </form>
</div>
@endsection

@push('scripts')
<script src="https://js.stripe.com/v3/"></script>
<script src="https://www.paypal.com/sdk/js?client-id={{ config('services.paypal.client_id') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('checkout-form');
        const shippingAddressInput = document.getElementById('shipping_address');
        const shippingMethodSelect = document.getElementById('shipping_method');
        const subtotalSpan = document.getElementById('subtotal');
        const shippingCostSpan = document.getElementById('shipping_cost');
        const totalSpan = document.getElementById('total');
        const addressFeedback = document.getElementById('address-feedback');
        const paymentMethodSelect = document.getElementById('payment_method');
        const stripeFields = document.getElementById('stripe_fields');
        const paypalFields = document.getElementById('paypal_fields');
        const submitButton = document.getElementById('submit-button');

        // Stripe setup
        const stripe = Stripe('{{ config('services.stripe.key') }}');
        const elements = stripe.elements();
        const cardElement = elements.create('card');
        cardElement.mount('#card-element');

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

        paymentMethodSelect.addEventListener('change', function() {
            if (this.value === 'stripe') {
                stripeFields.style.display = 'block';
                paypalFields.style.display = 'none';
                submitButton.style.display = 'block';
            } else if (this.value === 'paypal') {
                stripeFields.style.display = 'none';
                paypalFields.style.display = 'block';
                submitButton.style.display = 'none';
            }
        });

        // PayPal setup
        paypal.Buttons({
            createOrder: function(data, actions) {
                return actions.order.create({
                    purchase_units: [{
                        amount: {
                            value: totalSpan.textContent.replace('$', '')
                        }
                    }]
                });
            },
            onApprove: function(data, actions) {
                return actions.order.capture().then(function(details) {
                    // Handle successful payment
                    alert('Transaction completed by ' + details.payer.name.given_name);
                    // You can add code here to submit the form or make an AJAX call to your server
                });
            }
        }).render('#paypal-button-container');

        form.addEventListener('submit', async function(event) {
            event.preventDefault();
            if (paymentMethodSelect.value === 'stripe') {
                const {token, error} = await stripe.createToken(cardElement);
                if (error) {
                    const errorElement = document.getElementById('card-errors');
                    errorElement.textContent = error.message;
                } else {
                    // Insert the token ID into the form so it gets submitted to the server
                    const hiddenInput = document.createElement('input');
                    hiddenInput.setAttribute('type', 'hidden');
                    hiddenInput.setAttribute('name', 'stripeToken');
                    hiddenInput.setAttribute('value', token.id);
                    form.appendChild(hiddenInput);
                    form.submit();
                }
            } else {
                form.submit();
            }
        });

        updateOrderSummary();
    });
</script>
@endpush
