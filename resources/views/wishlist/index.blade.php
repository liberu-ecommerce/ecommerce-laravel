@extends('layouts.app')

@section('content')
<div class="container">
    <h1>My Wishlist</h1>
    @if(session('share_url'))
        <div class="alert alert-success">
            Your wishlist share URL: <a href="{{ session('share_url') }}">{{ session('share_url') }}</a>
        </div>
    @endif
    <form action="{{ route('wishlist.share') }}" method="POST">
        @csrf
        <button type="submit" class="btn btn-primary mb-3">Share Wishlist</button>
    </form>
    @if($wishlist->isEmpty())
        <p>Your wishlist is empty.</p>
    @else
        <div class="row">
            @foreach($wishlist as $item)
                <div class="col-md-4 mb-3">
                    <div class="card">
                        <img src="/images/placeholder.png" alt="{{ $item->product->name }}" class="card-img-top">
                        <div class="card-body">
                            <h5 class="card-title">{{ $item->product->name }}</h5>
                            <p class="card-text">{{ $item->product->description }}</p>
                            <p class="card-text"><strong>Price:</strong> ${{ number_format($item->product->price, 2) }}</p>
                            <form action="{{ route('wishlist.remove', $item->product) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">Remove from Wishlist</button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection