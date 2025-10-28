@props([
    'entity',
    'title' => null,
    'icon' => null,
    'description' => null,
])
@php
    $iconMap = [
        'items' => 'archive-box',
        'partners' => 'user-group',
        'countries' => 'globe-europe-africa',
        'languages' => 'language',
    ];
    $resolvedIcon = $icon ?: ($iconMap[$entity] ?? 'square-3-stack-3d');
    $iconComponent = 'heroicon-o-'.$resolvedIcon;
    $colorIconBg = [
        'items' => 'bg-teal-600',
        'partners' => 'bg-yellow-500',
        'countries' => 'bg-indigo-600',
        'languages' => 'bg-fuchsia-600',
    ][$entity] ?? 'bg-gray-600';
    $accentText = [
        'items' => 'text-teal-700',
        'partners' => 'text-yellow-700',
        'countries' => 'text-indigo-700',
        'languages' => 'text-fuchsia-700',
    ][$entity] ?? 'text-gray-700';
@endphp
<div {{ $attributes->merge(['class' => 'flex items-center justify-between mb-6']) }}>
    <div class="flex-1">
        <h1 class="text-2xl font-semibold text-gray-800 flex items-center gap-2">
            <span class="inline-flex items-center justify-center p-2 rounded-md {{ $colorIconBg }} text-white">
                <x-dynamic-component :component="$iconComponent" class="w-5 h-5" />
            </span>
            <span class="{{ $accentText }}">{{ $title ?? \Illuminate\Support\Str::title($entity) }}</span>
        </h1>
        @if($description)
            <p class="mt-2 text-sm text-gray-600">
                {{ $description }}
            </p>
        @endif
    </div>
    @if(isset($action) || trim($slot))
        <div class="flex gap-2">{{ $action ?? $slot }}</div>
    @endif
</div>