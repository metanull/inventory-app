@props([
    'field',
    'label',
])

@php
$currentSort = request()->query('sort', 'created_at');
$currentDir  = request()->query('dir', 'desc');
$isSorted    = $currentSort === $field;
$nextDir     = $isSorted && $currentDir === 'asc' ? 'desc' : 'asc';
$href        = request()->fullUrlWithQuery(['sort' => $field, 'dir' => $nextDir, 'page' => 1]);
@endphp

<th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
    <a href="{{ $href }}"
       class="group flex items-center space-x-1 hover:text-gray-700 transition-colors duration-200">
        <span>{{ $label }}</span>
        <span class="flex flex-col">
            @if($isSorted)
                @if($currentDir === 'asc')
                    <x-heroicon-s-chevron-up class="w-3 h-3 text-gray-600" />
                @else
                    <x-heroicon-s-chevron-down class="w-3 h-3 text-gray-600" />
                @endif
            @else
                <x-heroicon-s-chevron-up class="w-3 h-3 text-gray-300 group-hover:text-gray-400" />
            @endif
        </span>
    </a>
</th>