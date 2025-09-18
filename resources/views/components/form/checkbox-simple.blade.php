@props([
    'name',
    'label' => '',
    'value' => '1',
    'checked' => false,
    'class' => ''
])

@php
$oldValue = old($name, $checked ? '1' : '0');
$isChecked = $oldValue === '1' || $oldValue === 1 || $oldValue === true;
@endphp

<label class="inline-flex items-center gap-2 {{ $class }}">
    <input type="hidden" name="{{ $name }}" value="0" />
    <input type="checkbox" 
           name="{{ $name }}" 
           value="{{ $value }}" 
           @checked($isChecked)
           class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
    <span>{{ $label ?: ($slot ?? ucfirst(str_replace('_', ' ', $name))) }}</span>
</label>
@error($name)
    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
@enderror