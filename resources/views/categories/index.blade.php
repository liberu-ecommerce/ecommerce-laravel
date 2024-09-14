@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        @if($categories->count() > 0)
            @foreach($categories as $category)
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">{{ $category->name }}</h5>
                            <p class="card-text">{{ $category->description }}</p>
                            <p class="text-muted">Products: {{ number_format($category->product_count) }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        @else
            <div class="col-12">
                <p>No categories available.</p>
            </div>
        @endif
    </div>
    <div class="row">
        <div class="col-12 d-flex justify-content-center">
            {{ $categories->links() }}
        </div>
    </div>
</div>
@endsection
