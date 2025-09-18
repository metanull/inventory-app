@props([
    'entity' => null,
    'mobileOptimized' => true,
])

@php
$c = $entity ? $entityColor($entity) : [];
$focusClasses = $c['focus'] ?? 'focus:ring-indigo-500 focus:border-indigo-500';

// Mobile optimization classes
$mobileClasses = $mobileOptimized ? 'touch:h-12 touch:text-base' : '';

$classes = implode(' ', [
    'w-full rounded-md border-gray-300 shadow-sm',
    $focusClasses,
    $mobileClasses,
    'sm:text-sm', // Keep normal size on larger screens
]);
@endphp

<select {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</select>