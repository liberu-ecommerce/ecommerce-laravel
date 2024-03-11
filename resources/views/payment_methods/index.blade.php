@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Manage Payment Methods</h2>
    <div class="row">
        <div class="col-md-6">
            <form action="{{ route('payment_methods.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="name">Payment Method Name</label>
                    <input type="text" class="form-control" id="name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="details">Details</label>
                    <textarea class="form-control" id="details" name="details" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Add Payment Method</button>
            </form>
        </div>
    </div>
    <hr>
    <div class="row">
        @foreach ($paymentMethods as $method)
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">{{ $method->name }}</h5>
                    <p class="card-text">{{ $method->details }}</p>
                    <a href="{{ route('payment_methods.edit', $method->id) }}" class="btn btn-secondary">Edit</a>
                    <form action="{{ route('payment_methods.destroy', $method->id) }}" method="POST" onsubmit="return confirm('Are you sure?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                    <form action="{{ route('payment_methods.setDefault', $method->id) }}" method="POST">
                        @csrf
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_default" id="defaultCheck{{ $method->id }}" {{ $method->is_default ? 'checked' : '' }}>
                            <label class="form-check-label" for="defaultCheck{{ $method->id }}">
                                Set as Default
                            </label>
                        </div>
                        <button type="submit" class="btn btn-info">Update</button>
                    </form>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Optional JavaScript for handling specific actions on this page
</script>
@endpush
