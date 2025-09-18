@props([
    'contexts' => null,
    'value' => null,
    'name' => 'context_id',
    'label' => 'Context',
    'placeholder' => 'Select a context...',
    'required' => false,
])

@php
    $contexts = $contexts ?: \App\Models\Context::orderBy('internal_name')->get();
@endphp

<x-form.select 
    :name="$name" 
    :value="$value" 
    :required="$required"
    :placeholder="$placeholder"
>
    @foreach($contexts as $context)
        <option value="{{ $context->id }}" @selected(old($name, $value) == $context->id)>
            {{ $context->internal_name }}
        </option>
    @endforeach
</x-form.select>