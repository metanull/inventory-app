@props([
    'name' => 'extra',
    'label' => 'Additional Metadata',
    'value' => null,
    'required' => false,
    'variant' => 'gray',
])

@php
    // Handle different input formats
    $initialData = null;
    if ($value) {
        if (is_string($value)) {
            $initialData = json_decode($value, true);
        } elseif (is_object($value)) {
            $initialData = json_decode(json_encode($value), true);
        } elseif (is_array($value)) {
            $initialData = $value;
        }
    }
@endphp

<x-form.field :label="$label" :name="$name" :variant="$variant" :required="$required">
    @livewire('key-value-editor', ['initialData' => $initialData, 'componentName' => $name])
</x-form.field>
