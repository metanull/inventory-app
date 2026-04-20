@props([
    'label',
    'field',
    'currentSort' => null,
    'currentDirection' => 'desc',
    'url',
    'query' => [],
])

@php
    $isSorted = $currentSort === $field;
    $nextDirection = $isSorted && $currentDirection === 'asc' ? 'desc' : 'asc';
    $href = $url.'?'.http_build_query(array_filter(array_merge($query, [
        'sort' => $field,
        'direction' => $nextDirection,
        'page' => 1,
    ]), static fn (mixed $value): bool => $value !== null && $value !== [] && $value !== ''));
@endphp

<th {{ $attributes->class('px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider') }}>
    <a href="{{ $href }}" class="group flex items-center space-x-1 hover:text-gray-700 transition-colors duration-200">
        <span>{{ $label }}</span>
        <span class="flex flex-col">
            @if($isSorted)
                @if($currentDirection === 'asc')
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