@props([
    'size' => 'md',
    'variant' => 'primary',
    'entity' => null,
    'mobileOptimized' => true,
])

@php
$c = $entity ? $entityColor($entity) : [];

$sizeClasses = [
    'xs' => 'px-2 py-1 text-xs',
    'sm' => 'px-3 py-1.5 text-sm',
    'md' => 'px-4 py-2 text-sm',
    'lg' => 'px-5 py-2.5 text-base',
    'xl' => 'px-6 py-3 text-lg',
];

// Mobile optimization: increase touch target size
$mobileSizeClasses = [
    'xs' => 'touch:px-3 touch:py-2 touch:text-sm',
    'sm' => 'touch:px-4 touch:py-2.5 touch:text-base',
    'md' => 'touch:px-5 touch:py-3 touch:text-base',
    'lg' => 'touch:px-6 touch:py-3.5 touch:text-lg',
    'xl' => 'touch:px-7 touch:py-4 touch:text-xl',
];

$variantClasses = [
    'primary' => $c['button'] ?? 'bg-indigo-600 hover:bg-indigo-700 text-white border border-transparent',
    'secondary' => 'bg-white hover:bg-gray-50 text-gray-700 border border-gray-300',
    'danger' => 'bg-red-600 hover:bg-red-700 text-white border border-transparent',
    'ghost' => 'bg-transparent hover:bg-gray-100 text-gray-700 border border-transparent',
];

$baseClasses = 'inline-flex items-center justify-center font-medium rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors';

// Add mobile-specific touch improvements
$mobileClasses = $mobileOptimized ? 'touch:min-h-[44px] touch:min-w-[44px]' : '';

$classes = implode(' ', [
    $baseClasses,
    $sizeClasses[$size] ?? $sizeClasses['md'],
    $mobileOptimized ? ($mobileSizeClasses[$size] ?? $mobileSizeClasses['md']) : '',
    $variantClasses[$variant] ?? $variantClasses['primary'],
    $mobileClasses,
]);
@endphp

<button {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</button>