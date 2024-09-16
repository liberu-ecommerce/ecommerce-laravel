@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        @if($tags->count() > 0)
            @foreach($tags as $tag)
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">{{ $tag->name }}</h5>
                            <p class="card-text">{{ $tag->description }}</p>
                            <p class="text-muted">Products: {{ number_format($tag->product_count) }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        @else
            <div class="col-12">
                <p>No tags available.</p>
            </div>
        @endif
    </div>
    <div class="row">
        <div class="col-12 d-flex justify-content-center">
            {{ $tags->links() }}
        </div>
    </div>
</div>
@endsection
