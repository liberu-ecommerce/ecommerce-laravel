@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Edit Site Setting</h1>
    <div class="card" style="border-radius: 15px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); padding: 20px;">
        <form action="{{ route('site_settings.update', $setting->id) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="form-group">
                <label for="name">Name</label>
                <input type="text" class="form-control rounded-field" id="name" name="name" value="{{ $setting->name }}" readonly>
            </div>
            
            <div class="form-group">
                <label for="value">Value</label>
                <input type="text" class="form-control rounded-field" id="value" name="value" value="{{ $setting->value }}" required>
            </div>
            
            <div class="form-group">
                <label for="description">Description (Optional)</label>
                <textarea class="form-control rounded-field" id="description" name="description">{{ $setting->description }}</textarea>
            </div>
            
            <button type="submit" class="btn btn-primary rounded-button">Update</button>
            <a href="{{ route('site_settings.index') }}" class="btn btn-secondary rounded-button">Back</a>
        </form>
    </div>
</div>

<style>
    .rounded-field {
        border-radius: 15px;
        box-shadow: inset 0 2px 5px rgba(0, 0, 0, 0.1);
    }

    .rounded-button {
        border-radius: 15px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        padding: 10px 20px;
    }

    .card {
        border-radius: 15px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        padding: 20px;
        background-color: #fff;
    }
</style>
@endsection
