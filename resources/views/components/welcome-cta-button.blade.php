{{-- Welcome CTA Button Component --}}
@props(['href', 'variant' => 'primary'])

@php
$classes = match($variant) {
    'primary' => 'bg-blue-800 hover:bg-blue-700 focus:ring-blue-500 text-white',
    'secondary' => 'bg-white hover:bg-gray-50 focus:ring-blue-500 text-blue-800 border border-blue-200',
    'outline' => 'border border-white hover:bg-white hover:text-blue-800 focus:ring-white text-white',
    default => 'bg-blue-800 hover:bg-blue-700 focus:ring-blue-500 text-white'
};
@endphp

<a href="{{ $href }}" 
   {{ $attributes->merge(['class' => "inline-flex items-center px-6 py-3 rounded-lg font-semibold text-sm transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 {$classes}"]) }}>
    {{ $slot }}
</a>
