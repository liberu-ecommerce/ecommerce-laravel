@extends('layouts.master')

@section('content')
<form action="{{ route('orders.store') }}" method="POST">
    @csrf
    <div class="form-group">
        <label for="product">Product</label>
        <select name="product_id" id="product" class="form-control">
            @foreach($products as $product)
                <option value="{{ $product->id }}">{{ $product->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="form-group">
        <label for="quantity">Quantity</label>
        <input type="number" name="quantity" id="quantity" class="form-control" required>
    </div>
    <div class="form-group">
        <label for="shipping_address">Shipping Address</label>
        <input type="text" name="shipping_address" id="shipping_address" class="form-control" required>
    </div>
    <div class="form-group">
        <label for="card_number">Card Number</label>
        <input type="text" name="card_number" id="card_number" class="form-control" required>
    </div>
    <div class="form-group">
        <label for="expiration_date">Expiration Date</label>
        <input type="text" name="expiration_date" id="expiration_date" class="form-control" required>
    </div>
    <div class="form-group">
        <label for="cvv">CVV</label>
        <input type="number" name="cvv" id="cvv" class="form-control" required>
    </div>
    <button type="submit" class="btn btn-primary">Place Order</button>
    @error('message')
        <div class="alert alert-danger">{{ $message }}</div>
    @enderror
</form>
@endsection
