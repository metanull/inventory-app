@props([
    'name' => 'description',
    'label' => 'Description',
    'value' => null,
    'rows' => 8,
    'required' => false,
    'helpText' => null,
])

@livewire('markdown-editor', [
    'initialContent' => $value,
    'componentName' => $name,
    'label' => $label,
    'rows' => $rows,
    'required' => $required,
    'helpText' => $helpText,
])
