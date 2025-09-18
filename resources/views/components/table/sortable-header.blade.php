@props([
    'field',
    'label',
    'sortBy',
    'sortDirection'
])

@php
$isSorted = $sortBy === $field;
$nextDirection = $isSorted && $sortDirection === 'asc' ? 'desc' : 'asc';
@endphp

<th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
    <button wire:click="sortBy('{{ $field }}')" 
            class="group flex items-center space-x-1 hover:text-gray-700 transition-colors duration-200">
        <span>{{ $label }}</span>
        <span class="flex flex-col">
            @if($isSorted)
                @if($sortDirection === 'asc')
                    <x-heroicon-s-chevron-up class="w-3 h-3 text-gray-600" />
                @else
                    <x-heroicon-s-chevron-down class="w-3 h-3 text-gray-600" />
                @endif
            @else
                <x-heroicon-s-chevron-up class="w-3 h-3 text-gray-300 group-hover:text-gray-400" />
            @endif
        </span>
    </button>
</th>