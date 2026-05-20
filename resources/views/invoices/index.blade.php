@extends('layouts.app')

@section('content')
<div class="container">
    @livewire('invoices.index')
</div>
@endsection

@push('scripts')
<script>
    // Livewire Scripts
</script>
@endpush
