@props([
    'datetime' => null,
    'format' => 'Y-m-d H:i',
])

@if($datetime)
    {{ optional($datetime)->format($format) }}
@else
    —
@endif