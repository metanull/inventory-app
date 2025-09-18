@props([
    'partner' => null,
])

@if($partner)
    {{ $partner->internal_name }}
@else
    —
@endif