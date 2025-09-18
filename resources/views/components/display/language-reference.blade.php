@props([
    'language' => null,
])

@if($language)
    {{ $language->internal_name }} ({{ $language->id }})
@else
    —
@endif