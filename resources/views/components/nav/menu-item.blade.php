@props([
    'route',
    'routePattern',
    'entity',
    'icon' => null,
    'label',
    'mobile' => false,
])

@php
    $c = $entityColor($entity);
    $isActive = request()->routeIs($routePattern);
    $iconComponent = $icon ? 'heroicon-o-' . $icon : null;
    $badgeClass = $c['badge'];
    
    if ($mobile) {
        $classes = $isActive 
            ? $badgeClass . ' font-medium' 
            : 'text-gray-600 hover:bg-gray-50 hover:text-gray-800';
        $baseClass = 'block px-2 py-1 rounded';
    } else {
        $classes = $isActive 
            ? $badgeClass . ' font-medium' 
            : 'text-gray-700 hover:bg-gray-50';
        $baseClass = 'flex items-center gap-2 px-3 py-2 text-sm';
    }
@endphp

<a href="{{ $route }}" class="{{ $baseClass }} {{ $classes }}">
    @if($iconComponent && !$mobile)
        <x-dynamic-component :component="$iconComponent" class="w-4 h-4" />
    @endif
    {{ $label }}
</a>
