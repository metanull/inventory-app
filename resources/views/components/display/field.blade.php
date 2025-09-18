@props([
    'label' => '',
    'value' => null,
])

<div>
    <dt class="text-sm font-medium text-gray-500">{{ $label }}</dt>
    <dd class="mt-1 text-sm text-gray-900">
        @if($value !== null && $value !== '')
            {{ $slot->isNotEmpty() ? $slot : $value }}
        @else
            {{ $slot->isNotEmpty() ? $slot : '—' }}
        @endif
    </dd>
</div>