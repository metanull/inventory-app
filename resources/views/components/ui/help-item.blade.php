@props([
    'syntax' => '',
    'description' => '',
    'colspan' => false,
])

<div {{ $attributes->merge(['class' => $colspan ? 'col-span-2' : '']) }}>
    <dt class="font-mono text-xs bg-white px-2 py-1 rounded border border-gray-200">
        {!! $syntax !!}
    </dt>
    <dd class="text-blue-900 text-xs">
        {{ $description }}
    </dd>
</div>
