@props([
    'active' => false,
    'icon' => null,
])

@php
$classes = $active
    ? 'px-4 py-2 font-medium text-sm transition border-b-2 border-indigo-600 text-indigo-600'
    : 'px-4 py-2 font-medium text-sm transition text-gray-500 hover:text-gray-700';
@endphp

<button 
    type="button"
    {{ $attributes->merge(['class' => $classes]) }}
>
    @if($icon){{ $icon }} @endif{{ $slot }}
</button>
