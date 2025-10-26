@props(['language' => null, 'link' => true])

@php($c = $entityColor('languages'))

@if($language)
    @if($link)
        <a 
            href="{{ route('languages.show', $language) }}" 
            class="{{ $c['accentLink'] }}"
        >
            {{ $language->internal_name }}
        </a>
    @else
        {{ $language->internal_name }}
    @endif
@else
    <span class="text-gray-400">{{ $language?->id ?? 'N/A' }}</span>
@endif