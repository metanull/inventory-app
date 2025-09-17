@props([
    'entity',
    'title' => null,
    'icon' => null,
])
@php
    $iconMap = [
        'items' => 'archive-box',
        'partners' => 'user-group',
    ];
    $resolvedIcon = $icon ?: ($iconMap[$entity] ?? 'square-3-stack-3d');
    $iconComponent = 'heroicon-o-'.$resolvedIcon;
    $colorIconBg = [
        'items' => 'bg-teal-600',
        'partners' => 'bg-yellow-500',
    ][$entity] ?? 'bg-gray-600';
    $accentText = [
        'items' => 'text-teal-700',
        'partners' => 'text-yellow-700',
    ][$entity] ?? 'text-gray-700';
@endphp
<div {{ $attributes->merge(['class' => 'flex items-center justify-between mb-6']) }}>
    <h1 class="text-2xl font-semibold text-gray-800 flex items-center gap-2">
        <span class="inline-flex items-center justify-center p-2 rounded-md {{ $colorIconBg }} text-white">
            <x-dynamic-component :component="$iconComponent" class="w-5 h-5" />
        </span>
        <span class="{{ $accentText }}">{{ $title ?? \Illuminate\Support\Str::title($entity) }}</span>
    </h1>
    @if(trim($slot))
        <div class="flex gap-2">{{ $slot }}</div>
    @endif
</div>