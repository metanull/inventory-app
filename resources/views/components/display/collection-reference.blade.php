@props(['collection' => null, 'link' => true])

@php($c = $entityColor('collections'))

@if($collection)
    @if($link)
        <a 
            href="{{ route('collections.show', $collection) }}" 
            class="{{ $c['accentLink'] }}"
        >
            {{ $collection->internal_name }}
        </a>
    @else
        {{ $collection->internal_name }}
    @endif
@else
    <span class="text-gray-400">N/A</span>
@endif
