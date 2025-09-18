@props([
    'type' => 'text',
    'name' => '',
    'value' => '',
    'placeholder' => '',
    'required' => false,
    'readonly' => false,
    'maxlength' => null,
    'class' => '',
])

<input 
    type="{{ $type }}" 
    name="{{ $name }}" 
    value="{{ $value }}" 
    @if($placeholder) placeholder="{{ $placeholder }}" @endif
    @if($required) required @endif
    @if($readonly) readonly @endif
    @if($maxlength) maxlength="{{ $maxlength }}" @endif
    {{ $attributes->merge([
        'class' => 'block w-full px-3 py-2 rounded-md shadow-sm sm:text-sm border border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 ' . $class
    ]) }}
/>