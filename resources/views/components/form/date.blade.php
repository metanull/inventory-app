@props([
    'name',
    'label',
    'value' => '',
    'required' => false,
    'variant' => 'white', // 'white' or 'gray'
    'placeholder' => ''
])

@php
$bgClass = $variant === 'gray' ? 'bg-gray-50' : '';
$oldValue = old($name, $value);
// Handle date formatting if the value is a date object
if ($oldValue && is_object($oldValue) && method_exists($oldValue, 'format')) {
    $oldValue = $oldValue->format('Y-m-d');
}
@endphp

<div class="px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 {{ $bgClass }}">
    <dt class="text-sm font-medium text-gray-700">
        {{ $label }}
        @if($required)
            <span class="text-red-500">*</span>
        @endif
    </dt>
    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
        <input type="date" 
               name="{{ $name }}" 
               value="{{ $oldValue }}" 
               @if($required) required @endif
               @if($placeholder) placeholder="{{ $placeholder }}" @endif
               class="block w-60 px-3 py-2 rounded-md shadow-sm sm:text-sm border border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" />
        @error($name)
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </dd>
</div>