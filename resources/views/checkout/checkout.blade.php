@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Checkout</h1>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h4>Order Details</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('checkout.process') }}" method="POST" id="checkout-form">
                        @csrf
                        <div class="form-group mb-3">
                            <label for="email">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>

                        @if($hasPhysicalProducts)
                            <div class="form-group mb-3">
                                <label for="shipping_address">Shipping Address</label>
                                <textarea class="form-control" id="shipping_address" name="shipping_address" required rows="3"></textarea>
                            </div>

                            <div class="form-group mb-3">
                                <label for="shipping_method">Shipping Method</label>
                                <select class="form-control" id="shipping_method" name="shipping_method_id" required>
                                    @foreach($shippingMethods as $method)
                                        <option value="{{ $method->id }}" data-base-rate="{{ $method->base_rate }}">
                                            {{ $method->name }} - ${{ number_format($method->base_rate, 2) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="dropship" name="dropship">
                                <label class="form-check-label" for="dropship">
                                    Ship directly to recipient (Drop shipping)
                                </label>
                            </div>

                            <div id="recipient-info" class="d-none mb-3">
                                <div class="form-group mb-3">
                                    <label for="recipient_name">Recipient Name</label>
                                    <input type="text" class="form-control" id="recipient_name" name="recipient_name">
                                </div>
                                <div class="form-group mb-3">
                                    <label for="recipient_email">Recipient Email</label>
                                    <input type="email" class="form-control" id="recipient_email" name="recipient_email">
                                </div>
                                <div class="form-group mb-3">
                                    <label for="gift_message">Gift Message (Optional)</label>
                                    <textarea class="form-control" id="gift_message" name="gift_message" rows="2"></textarea>
                                </div>
                            </div>
                        @endif

                        @if($total > 0)
                            <div class="form-group mb-3">
                                <label for="payment_method">Payment Method</label>
                                <select class="form-control" id="payment_method" name="payment_method" required>
                                    <option value="stripe">Credit Card (Stripe)</option>
                                    <option value="paypal">PayPal</option>
                                </select>
                            </div>

                            <div id="stripe-payment" class="payment-method-form">
                                <div id="card-element" class="form-control mb-3">
                                    <!-- Stripe Elements Placeholder -->
                                </div>
                                <div id="card-errors" role="alert" class="text-danger mb-3"></div>
                            </div>

                            <div id="paypal-payment" class="payment-method-form d-none">
                                <div id="paypal-button-container" class="mb-3"></div>
                                <input type="hidden" name="paypal_payment_id" id="paypal_payment_id">
                            </div>
                        @endif

                        <button type="submit" class="btn btn-primary mt-4" id="submit-button">
                            {{ $total > 0 ? 'Complete Purchase' : 'Complete Order' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h4>Order Summary</h4>
                </div>
                <div class="card-body">
                    @foreach($cart as $productId => $item)
                        <div class="d-flex justify-content-between mb-2">
                            <span>{{ $item['name'] }} x {{ $item['quantity'] }}</span>
                            <span>${{ number_format($item['price'] * $item['quantity'], 2) }}</span>
                        </div>
                    @endforeach
                    <hr>
                    <div class="d-flex justify-content-between mb-2">
                        <strong>Subtotal:</strong>
                        <span>${{ number_format($total, 2) }}</span>
                    </div>
                    @if($hasPhysicalProducts)
                        <div class="d-flex justify-content-between mb-2" id="shipping-cost">
                            <strong>Shipping:</strong>
                            <span>$0.00</span>
                        </div>
                    @endif
                    <div class="d-flex justify-content-between">
                        <strong>Total:</strong>
                        <span id="total-amount">${{ number_format($total, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Toggle drop shipping recipient info
        const dropshipCheckbox = document.getElementById('dropship');
        const recipientInfo = document.getElementById('recipient-info');

        if (dropshipCheckbox) {
            dropshipCheckbox.addEventListener('change', function() {
                recipientInfo.classList.toggle('d-none', !this.checked);
            });
        }

        // Update shipping cost when shipping method changes
        const shippingMethodSelect = document.getElementById('shipping_method');
        const shippingCostElement = document.querySelector('#shipping-cost span');
        const totalAmountElement = document.getElementById('total-amount');
        let subtotal = {{ $total }};

        if (shippingMethodSelect) {
            shippingMethodSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                const shippingRate = parseFloat(selectedOption.dataset.baseRate);

                shippingCostElement.textContent = '$' + shippingRate.toFixed(2);
                const total = subtotal + shippingRate;
                totalAmountElement.textContent = '$' + total.toFixed(2);
            });

            // Trigger change to set initial shipping cost
            shippingMethodSelect.dispatchEvent(new Event('change'));
        }

        // Toggle payment methods
        const paymentMethodSelect = document.getElementById('payment_method');
        const stripePayment = document.getElementById('stripe-payment');
        const paypalPayment = document.getElementById('paypal-payment');

        if (paymentMethodSelect) {
            paymentMethodSelect.addEventListener('change', function() {
                if (this.value === 'stripe') {
                    stripePayment.classList.remove('d-none');
                    paypalPayment.classList.add('d-none');
                } else if (this.value === 'paypal') {
                    stripePayment.classList.add('d-none');
                    paypalPayment.classList.remove('d-none');
                }
            });
        }
    });
</script>

@if($total > 0)
<script src="https://js.stripe.com/v3/"></script>
<script src="https://www.paypal.com/sdk/js?client-id={{ config('services.paypal.client_id') }}&currency=USD"></script>
<script>
    // Stripe integration
    const stripe = Stripe('{{ config('services.stripe.key') }}');
    const elements = stripe.elements();
    const card = elements.create('card');
    card.mount('#card-element');

    const form = document.getElementById('checkout-form');
    const paymentMethodSelect = document.getElementById('payment_method');

    form.addEventListener('submit', async (event) => {
        if (paymentMethodSelect.value === 'stripe') {
            event.preventDefault();

            const {token, error} = await stripe.createToken(card);

            if (error) {
                const errorElement = document.getElementById('card-errors');
                errorElement.textContent = error.message;
            } else {
                const hiddenInput = document.createElement('input');
                hiddenInput.setAttribute('type', 'hidden');
                hiddenInput.setAttribute('name', 'stripeToken');
                hiddenInput.setAttribute('value', token.id);
                form.appendChild(hiddenInput);
                form.submit();
            }
        }
    });

    // PayPal integration
    if (document.getElementById('paypal-button-container')) {
        paypal.Buttons({
            createOrder: function(data, actions) {
                return actions.order.create({
                    purchase_units: [{
                        amount: {
                            value: document.getElementById('total-amount').textContent.replace('$', '')
                        }
                    }]
                });
            },
            onApprove: function(data, actions) {
                return actions.order.capture().then(function(details) {
                    document.getElementById('paypal_payment_id').value = details.id;
                    form.submit();
                });
            }
        }).render('#paypal-button-container');
    }
</script>
@endif
@endpush
@endsection