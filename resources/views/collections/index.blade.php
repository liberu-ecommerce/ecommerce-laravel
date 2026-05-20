@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        @if($collections->count() > 0)
            @foreach($collections as $collection)
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">{{ $collection->name }}</h5>
                            <p class="card-text">{{ $collection->description }}</p>
                            <p class="text-muted">Products: {{ number_format($collection->product_count) }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        @else
            <div class="col-12">
                <p>No collections available.</p>
            </div>
        @endif
    </div>
    <div class="row">
        <div class="col-12 d-flex justify-content-center">
            {{ $collections->links() }}
        </div>
    </div>
</div>
@endsection
