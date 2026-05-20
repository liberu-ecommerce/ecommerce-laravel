@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Site Settings</h1>
    <table class="table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Value</th>
                <th>Description</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($settings as $setting)

            @empty
                <tr>
                    <td colspan="4">No settings found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
