@props([
  'method' => 'POST',
  'action',
  'confirm' => 'Are you sure?',
  'color' => 'red', // red|indigo|gray
  'size' => 'xs', // xs|sm
  'icon' => 'trash', // heroicon name without prefix
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
    ];
    $classes = ($sizes[$size] ?? $sizes['xs']).' inline-flex items-center rounded-md border bg-white font-medium transition '.$palette[$color];
    $verbSpoof = strtoupper($method) !== 'POST' && strtoupper($method) !== 'GET';
@endphp
<button type="button" x-data @click="if(window.Livewire){ window.Livewire.dispatch('confirm-action', {
        title: @js($confirm),
        message: 'This operation cannot be undone.',
        confirmLabel: 'Delete',
        cancelLabel: 'Cancel',
        action: @js($action),
        method: @js($method),
        color: @js($color)
    }) }" class="{{ $classes }}">
    @if($icon === 'trash')<x-heroicon-o-trash class="w-4 h-4 mr-1" />@endif
    <span>{{ $slot }}</span>
</button>
