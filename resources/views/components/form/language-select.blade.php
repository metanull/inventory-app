@props([
    'languages' => null,
    'value' => null,
    'name' => 'language_id',
    'label' => 'Language',
    'placeholder' => 'Select a language...',
    'required' => false,
])

@php
    $languages = $languages ?? collect();
@endphp

<x-form.select 
    :name="$name" 
    :value="$value" 
    :required="$required"
    :placeholder="$placeholder"
>
    @foreach($languages as $language)
        <option value="{{ $language->id }}" @selected(old($name, $value) == $language->id)>
            {{ $language->internal_name }} ({{ $language->id }})
        </option>
    @endforeach
</x-form.select>