@props([
    'name' => 'extra',
    'label' => 'Additional Metadata',
    'value' => null,
    'required' => false,
    'variant' => 'gray',
])

<x-form.field :label="$label" :name="$name" :variant="$variant" :required="$required">
    @livewire('key-value-editor', ['initialData' => $value ? json_decode($value, true) : null, 'componentName' => $name])
</x-form.field>
