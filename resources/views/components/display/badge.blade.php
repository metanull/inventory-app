@props([
    'text' => '',
    'entity' => null,
    'variant' => 'default', // 'default', 'success', 'warning', 'info'
])

@php
    $baseClasses = 'px-2 py-0.5 text-xs rounded';
    
    if ($entity) {
        $c = $entityColor($entity);
        $classes = $baseClasses . ' ' . ($c['badge'] ?? 'bg-gray-100 text-gray-800');
    } else {
        $variantClasses = match($variant) {
            'success' => 'bg-green-100 text-green-800',
            'warning' => 'bg-yellow-100 text-yellow-800',
            'info' => 'bg-blue-100 text-blue-800',
            default => 'bg-gray-100 text-gray-800',
        };
        $classes = $baseClasses . ' ' . $variantClasses;
    }
@endphp

<span class="{{ $classes }}">
    {{ $slot->isNotEmpty() ? $slot : $text }}
</span>