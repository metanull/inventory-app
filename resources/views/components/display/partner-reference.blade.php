@props(['partner' => null, 'link' => true])

@php($c = $entityColor('partners'))

@if($partner)
    @if($link)
        <a 
            href="{{ route('partners.show', $partner) }}" 
            class="{{ $c['accentLink'] }}"
        >
            {{ $partner->internal_name }}
        </a>
    @else
        {{ $partner->internal_name }}
    @endif
@else
    <span class="text-gray-400">N/A</span>
@endif