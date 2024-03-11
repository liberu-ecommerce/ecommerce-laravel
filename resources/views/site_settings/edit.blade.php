@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Edit Site Setting</h1>
    <form action="{{ route('site_settings.update', $setting->id) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="form-group">
            <label for="name">Name</label>
            <input type="text" class="form-control" id="name" name="name" value="{{ $setting->name }}" readonly>
        </div>
        
        <div class="form-group">
            <label for="value">Value</label>
            <input type="text" class="form-control" id="value" name="value" value="{{ $setting->value }}" required>
        </div>
        
        <div class="form-group">
            <label for="description">Description (Optional)</label>
            <textarea class="form-control" id="description" name="description">{{ $setting->description }}</textarea>
        </div>
        
        <button type="submit" class="btn btn-primary">Update</button>
        <a href="{{ route('site_settings.index') }}" class="btn btn-secondary">Back</a>
    </form>
</div>
@endsection
