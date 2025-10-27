@props([
    'heading',
    'border' => true,
])

<div {{ $attributes->merge(['class' => ($border ? 'border-b border-gray-200 pb-6' : '')]) }}>
    @if($heading)
        <h3 class="text-lg font-medium text-gray-900 mb-4">{{ $heading }}</h3>
    @endif
    
    <div class="space-y-4">
        {{ $slot }}
    </div>
</div>
