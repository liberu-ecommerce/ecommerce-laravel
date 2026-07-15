{{-- size-4 keeps the box itself visible while its <label> wrapper supplies the
     24px target. accent-color is the native way to brand a checkbox and needs no
     custom appearance hacks. --}}
<input type="checkbox" {!! $attributes->merge(['class' => 'size-4 rounded border-stroke text-primary-700 accent-primary-700 focus:ring-2 focus:ring-primary-600']) !!}>
