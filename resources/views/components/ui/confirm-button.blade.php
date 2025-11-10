@props([
    'action' => null,
    'method' => 'DELETE',
    'confirmMessage' => 'Are you sure?',
    'variant' => 'danger',
    'size' => 'sm',
    'icon' => 'trash',
    'entity' => null,
])

@php
    $sizes = [
        'xs' => 'px-2.5 py-1.5 text-xs',
        'sm' => 'px-3 py-2 text-sm',
    ];
    $palette = [
        'red' => 'border-red-200 text-red-600 hover:text-red-700 hover:bg-red-50',
        'indigo' => 'border-indigo-200 text-indigo-600 hover:text-indigo-700 hover:bg-indigo-50',
        'gray' => 'border-gray-200 text-gray-600 hover:text-gray-800 hover:bg-gray-50',
        'white' => 'bg-white/90 text-red-600 hover:bg-white hover:text-red-700 shadow-sm',
    ];
    $color = $variant === 'danger' ? 'red' : ($variant === 'warning' ? 'indigo' : 'gray');
    $hasSlot = trim($slot ?? '') !== '';
    $iconOnly = !$hasSlot;
    
    $baseClasses = 'inline-flex items-center rounded-md font-medium transition';
    if ($iconOnly) {
        $classes = $baseClasses.' p-1 '.$palette[$color];
    } else {
        $classes = $baseClasses.' border '.($sizes[$size] ?? $sizes['sm']).' '.$palette[$color];
    }
@endphp

<button type="button" 
        x-data 
        @click="$dispatch('confirm-action', {
            title: @js($confirmMessage),
            message: 'This operation cannot be undone.',
            confirmLabel: 'Delete',
            cancelLabel: 'Cancel',
            action: @js($action),
            method: @js($method),
            color: @js($color)
        })" 
        class="{{ $classes }}"
        {{ $attributes }}>
    @if($icon === 'trash')
        <x-heroicon-o-trash class="w-4 h-4{{ $hasSlot ? ' mr-1' : '' }}" />
    @elseif($icon === 'x-mark')
        <x-heroicon-o-x-mark class="w-4 h-4{{ $hasSlot ? ' mr-1' : '' }}" />
    @endif
    @if($hasSlot)
        <span>{{ $slot }}</span>
    @endif
</button>
