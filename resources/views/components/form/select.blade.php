@props([
    'name' => '',
    'value' => '',
    'required' => false,
    'placeholder' => 'Select an option...',
    'options' => [],
    'class' => '',
])

<select 
    name="{{ $name }}" 
    @if($required) required @endif
    {{ $attributes->merge([
        'class' => 'block w-full px-3 py-2 rounded-md shadow-sm sm:text-sm border border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 ' . $class
    ]) }}
>
    @if($placeholder)
        <option value="">{{ $placeholder }}</option>
    @endif
    
    {{ $slot }}
    
    @if(!empty($options))
        @foreach($options as $optionValue => $optionLabel)
            <option value="{{ $optionValue }}" @selected(old($name, $value) == $optionValue)>
                {{ $optionLabel }}
            </option>
        @endforeach
    @endif
</select>