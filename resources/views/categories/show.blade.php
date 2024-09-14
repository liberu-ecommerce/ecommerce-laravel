@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ $category->name }}</div>
                <div class="card-body">
                    <h3>Category Page</h3>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 
