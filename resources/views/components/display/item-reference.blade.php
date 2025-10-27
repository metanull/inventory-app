@props(['item', 'link' => true])

@php($c = $entityColor('items'))

@if($item)
    @if($link)
        <a 
            href="{{ route('items.show', $item) }}" 
            class="{{ $c['accentLink'] }}"
        >
            {{ $item->internal_name }}
        </a>
    @else
        {{ $item->internal_name }}
    @endif
@else
    <span class="text-gray-400">N/A</span>
@endif
