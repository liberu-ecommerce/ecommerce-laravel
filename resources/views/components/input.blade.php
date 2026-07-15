@props(['disabled' => false])

{{-- .form-input carries the system's stroke and moss focus ring. The old inline
     classes hardcoded indigo, which is the framework's brand, not this one. --}}
<input {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'form-input']) !!}>
