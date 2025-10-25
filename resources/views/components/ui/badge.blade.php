@props([
    'color' => 'gray',
    'variant' => 'default', // 'default' or 'pill'
    'size' => 'default', // 'default' or 'sm'
])

@php
    $baseClasses = 'inline-flex items-center font-medium';
    
    // Size classes
    $sizeClasses = match($size) {
        'sm' => 'px-2 py-0.5 text-xs',
        default => 'px-2.5 py-0.5 text-xs',
    };
    
    // Variant classes
    $variantClasses = match($variant) {
        'pill' => 'rounded-full',
        default => 'rounded',
    };
    
    // Color classes
    $colorClasses = match($color) {
        'blue' => 'bg-blue-100 text-blue-800',
        'green' => 'bg-green-100 text-green-800',
        'red' => 'bg-red-100 text-red-800',
        'yellow' => 'bg-yellow-100 text-yellow-800',
        'purple' => 'bg-purple-100 text-purple-800',
        'pink' => 'bg-pink-100 text-pink-800',
        'indigo' => 'bg-indigo-100 text-indigo-800',
        'teal' => 'bg-teal-100 text-teal-800',
        'orange' => 'bg-orange-100 text-orange-800',
        default => 'bg-gray-100 text-gray-800',
    };
    
    $classes = trim("$baseClasses $sizeClasses $variantClasses $colorClasses");
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</span>
