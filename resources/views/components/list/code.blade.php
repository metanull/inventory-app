@props([
    'value' => null,
    'uppercase' => true,
])

@php
    $display = is_scalar($value) ? trim((string) $value) : '';
    $display = $uppercase ? strtoupper($display) : $display;
@endphp

@if($display === '')
    <span class="text-gray-400">-</span>
@else
    <span class="inline-flex items-center rounded-md bg-slate-100 px-2 py-1 font-mono text-xs font-semibold uppercase tracking-wide text-slate-700 ring-1 ring-inset ring-slate-200">
        {{ $display }}
    </span>
@endif