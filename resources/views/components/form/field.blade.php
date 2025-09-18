@props([
    'label' => '',
    'name' => '',
    'variant' => 'white', // 'white' or 'gray'
    'required' => false,
])

<div class="px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 {{ $variant === 'gray' ? 'bg-gray-50' : '' }}">
    <dt class="text-sm font-medium text-gray-700">
        {{ $label }}@if($required)<span class="text-red-500">*</span>@endif
    </dt>
    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
        {{ $slot }}
        @if($name)
            @error($name)<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        @endif
    </dd>
</div>