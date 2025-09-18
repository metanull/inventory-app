@props([
    'label',
    'variant' => 'white', // 'white' or 'gray'
    'checkboxes' => []
])

@php
$bgClass = $variant === 'gray' ? 'bg-gray-50' : '';
@endphp

<div class="px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 {{ $bgClass }}">
    <dt class="text-sm font-medium text-gray-700">{{ $label }}</dt>
    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2 space-y-2">
        {{ $slot }}
    </dd>
</div>