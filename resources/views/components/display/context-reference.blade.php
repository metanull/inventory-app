@props([
    'context' => null,
])

@if($context)
    {{ $context->internal_name }}
@else
    —
@endif