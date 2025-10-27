@props([
    'variant' => 'primary',
    'size' => 'md',
    'icon' => null,
    'href' => null,
    'type' => 'button',
    'entity' => null,
])

@php
    // Size classes
    $sizeClasses = [
        'xs' => 'px-2 py-1 text-xs',
        'sm' => 'px-3 py-1.5 text-xs',
        'md' => 'px-4 py-2 text-sm',
        'lg' => 'px-6 py-3 text-base',
    ];

    // Entity color helper
    $entityColors = [
        'items' => ['bg' => 'bg-blue-600', 'hover' => 'hover:bg-blue-700', 'focus' => 'focus:ring-blue-500'],
        'partners' => ['bg' => 'bg-purple-600', 'hover' => 'hover:bg-purple-700', 'focus' => 'focus:ring-purple-500'],
        'collections' => ['bg' => 'bg-green-600', 'hover' => 'hover:bg-green-700', 'focus' => 'focus:ring-green-500'],
        'contexts' => ['bg' => 'bg-amber-600', 'hover' => 'hover:bg-amber-700', 'focus' => 'focus:ring-amber-500'],
    ];

    // Variant classes - support entity-aware variants
    if ($entity && isset($entityColors[$entity])) {
        $entityColor = $entityColors[$entity];
        $variantClasses = [
            'primary' => $entityColor['bg'] . ' ' . $entityColor['hover'] . ' ' . $entityColor['focus'] . ' text-white border-transparent',
            'secondary' => 'bg-gray-200 hover:bg-gray-300 focus:ring-gray-500 text-gray-900 border-transparent',
            'edit' => 'bg-blue-600 hover:bg-blue-700 focus:ring-blue-500 text-white border-transparent',
            'delete' => 'bg-red-600 hover:bg-red-700 focus:ring-red-500 text-white border-transparent',
            'danger' => 'bg-red-600 hover:bg-red-700 focus:ring-red-500 text-white border-transparent',
            'warning' => 'bg-yellow-600 hover:bg-yellow-700 focus:ring-yellow-500 text-white border-transparent',
            'success' => 'bg-green-600 hover:bg-green-700 focus:ring-green-500 text-white border-transparent',
            'ghost' => 'bg-gray-600 hover:bg-gray-700 focus:ring-gray-500 text-white border-transparent',
        ];
    } else {
        $variantClasses = [
            'primary' => 'bg-blue-600 hover:bg-blue-700 focus:ring-blue-500 text-white border-transparent',
            'secondary' => 'bg-gray-200 hover:bg-gray-300 focus:ring-gray-500 text-gray-900 border-transparent',
            'edit' => 'bg-blue-600 hover:bg-blue-700 focus:ring-blue-500 text-white border-transparent',
            'delete' => 'bg-red-600 hover:bg-red-700 focus:ring-red-500 text-white border-transparent',
            'danger' => 'bg-red-600 hover:bg-red-700 focus:ring-red-500 text-white border-transparent',
            'warning' => 'bg-yellow-600 hover:bg-yellow-700 focus:ring-yellow-500 text-white border-transparent',
            'success' => 'bg-green-600 hover:bg-green-700 focus:ring-green-500 text-white border-transparent',
            'ghost' => 'bg-gray-600 hover:bg-gray-700 focus:ring-gray-500 text-white border-transparent',
        ];
    }

    // Icon size classes
    $iconSizeClasses = [
        'xs' => 'w-3 h-3',
        'sm' => 'w-3 h-3',
        'md' => 'w-4 h-4',
        'lg' => 'w-5 h-5',
    ];

    // Build final classes
    $classes = 'inline-flex items-center border rounded font-semibold uppercase tracking-widest focus:outline-none focus:ring-2 focus:ring-offset-2 transition ease-in-out duration-150 ' 
        . ($sizeClasses[$size] ?? $sizeClasses['md']) . ' ' 
        . ($variantClasses[$variant] ?? $variantClasses['primary']);

    // Icon component mapping
    $iconComponents = [
        'plus' => 'heroicon-o-plus',
        'pencil' => 'heroicon-o-pencil',
        'trash' => 'heroicon-o-trash',
        'photo' => 'heroicon-o-photo',
        'arrow-up' => 'heroicon-o-arrow-up',
        'arrow-down' => 'heroicon-o-arrow-down',
        'arrows-up-down' => 'heroicon-o-arrows-up-down',
        'x-mark' => 'heroicon-o-x-mark',
        'check' => 'heroicon-o-check',
        'eye' => 'heroicon-o-eye',
        'document-text' => 'heroicon-o-document-text',
    ];

    $iconComponent = $icon && isset($iconComponents[$icon]) ? $iconComponents[$icon] : null;
    $iconSize = $iconSizeClasses[$size] ?? $iconSizeClasses['md'];
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if($iconComponent)
            <x-dynamic-component :component="$iconComponent" :class="'mr-1.5 ' . $iconSize" />
        @endif
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if($iconComponent)
            <x-dynamic-component :component="$iconComponent" :class="'mr-1.5 ' . $iconSize" />
        @endif
        {{ $slot }}
    </button>
@endif
