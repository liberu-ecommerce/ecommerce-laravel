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

        @if($hasPhysicalProducts)
            <div class="form-group">
                <label for="shipping_address">Shipping Address</label>
                <textarea class="form-control" id="shipping_address" name="shipping_address" required></textarea>
            </div>

            <div class="form-group">
                <label for="shipping_method">Shipping Method</label>
                <select class="form-control" id="shipping_method" name="shipping_method_id" required>
                    @foreach($shippingMethods as $method)
                        <option value="{{ $method->id }}" data-base-rate="{{ $method->base_rate }}">
                            {{ $method->name }} - ${{ number_format($method->base_rate, 2) }}
                        </option>
                    @endforeach
                </select>
            </div>
        @endif

        @if($total > 0)
            <div class="form-group">
                <label for="payment_method">Payment Method</label>
                <select class="form-control" id="payment_method" name="payment_method" required>
                    <option value="stripe">Credit Card (Stripe)</option>
                </select>
            </div>

            <div id="card-element">
                <!-- Stripe Elements Placeholder -->
            </div>
            <div id="card-errors" role="alert"></div>
        @endif

        <button type="submit" class="btn btn-primary mt-4">
            {{ $total > 0 ? 'Complete Purchase' : 'Complete Order' }}
        </button>
    </form>
</div>

@push('scripts')
@if($total > 0)
<script src="https://js.stripe.com/v3/"></script>
<script>
    const stripe = Stripe('{{ config('services.stripe.key') }}');
    const elements = stripe.elements();
    const card = elements.create('card');
    card.mount('#card-element');

    const form = document.getElementById('checkout-form');
    form.addEventListener('submit', async (event) => {
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
    });
</script>
@endif
@endpush
@endsection