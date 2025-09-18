@props([
    'name',
    'label',
    'value' => '1',
    'checked' => false,
    'variant' => 'white' // 'white' or 'gray'
])

@php
$bgClass = $variant === 'gray' ? 'bg-gray-50' : '';
$oldValue = old($name, $checked ? '1' : '0');
$isChecked = $oldValue === '1' || $oldValue === 1 || $oldValue === true;
@endphp

<div class="px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 {{ $bgClass }}">
    <dt class="text-sm font-medium text-gray-700">{{ $label }}</dt>
    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
        <label class="inline-flex items-center gap-2">
            <input type="hidden" name="{{ $name }}" value="0" />
            <input type="checkbox" 
                   name="{{ $name }}" 
                   value="{{ $value }}" 
                   @checked($isChecked)
                   class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
            <span>{{ $slot ?? ucfirst(str_replace('_', ' ', $name)) }}</span>
        </label>
        @error($name)
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </dd>
</div>