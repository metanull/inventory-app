@props(['country' => null, 'link' => true])

@php($c = $entityColor('countries'))

@if($country)
    @if($link)
        <a 
            href="{{ route('countries.show', $country) }}" 
            class="{{ $c['accentLink'] }}"
        >
            {{ $country->internal_name }}
        </a>
    @else
        {{ $country->internal_name }}
    @endif
@else
    <span class="text-gray-400">{{ $country?->id ?? 'N/A' }}</span>
@endif