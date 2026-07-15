@props(['for'])

@error($for)
    <p {{ $attributes->merge(['class' => 'text-label text-danger-700']) }}>{{ $message }}</p>
@enderror
