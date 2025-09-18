@props([
    'country' => null,
])

@if($country)
    {{ $country->internal_name }} ({{ $country->id }})
@else
    —
@endif