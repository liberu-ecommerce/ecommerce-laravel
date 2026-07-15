<button {{ $attributes->merge(['type' => 'button', 'class' => 'btn btn-secondary']) }}>
    {{ $slot }}
</button>
