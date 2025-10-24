@props(['latitude', 'longitude', 'mapZoom' => 16])

@if($latitude && $longitude)
    <a 
        href="https://maps.google.com/?q={{ $latitude }},{{ $longitude }}" 
        target="_blank"
        rel="noopener noreferrer"
        class="text-blue-600 hover:text-blue-800 underline"
    >
        {{ number_format($latitude, 6) }}, {{ number_format($longitude, 6) }}
        <svg class="inline-block w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
        </svg>
    </a>
@else
    <span class="text-gray-400">Not specified</span>
@endif
