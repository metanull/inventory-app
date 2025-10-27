@props([
    'name',
    'value' => null,
    'rows' => 4,
    'required' => false,
])

<textarea 
    name="{{ $name }}" 
    rows="{{ $rows }}"
    {{ $required ? 'required' : '' }}
    {{ $attributes->merge(['class' => 'block w-full px-3 py-2 rounded-md shadow-sm sm:text-sm border border-gray-300 focus:border-indigo-500 focus:ring-indigo-500']) }}
>{{ old($name, $value) }}</textarea>
