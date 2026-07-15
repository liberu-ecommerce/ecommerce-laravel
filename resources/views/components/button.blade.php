{{-- Sentence case, not uppercase+tracking: this system reserves all-caps for nothing,
     and a shouted "LOG IN" is the framework's voice rather than the store's. --}}
<button {{ $attributes->merge(['type' => 'submit', 'class' => 'btn btn-primary']) }}>
    {{ $slot }}
</button>
