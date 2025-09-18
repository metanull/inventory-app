@props([
    'label',
    'value',
    'format' => 'Y-m-d',
    'variant' => 'white' // 'white' or 'gray'
])

@php
$bgClass = $variant === 'gray' ? 'bg-gray-50' : '';
$displayValue = '—';
if ($value) {
    if (is_string($value)) {
        $displayValue = $value;
    } elseif (is_object($value) && method_exists($value, 'format')) {
        $displayValue = $value->format($format);
    }
}
@endphp

<div class="px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 {{ $bgClass }}">
    <dt class="text-sm font-medium text-gray-700">{{ $label }}</dt>
    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $displayValue }}</dd>
</div>