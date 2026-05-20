@props(['disabled' => false])

<input {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'px-3 py-2 bg-white text-gray-900 border-gray-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 rounded-md shadow-sm transition']) !!}>
