@props([
    'name' => '',
    'label' => '',
    'value' => null,
    'displayField' => 'name', // Field to display (e.g., 'internal_name', 'name')
    'valueField' => 'id', // Field to use as the value (default: 'id')
    'placeholder' => 'Select an option...',
    'required' => false,
    'searchPlaceholder' => 'Type to search...',
    'entity' => null, // For entity color theming
    
    // For static options (e.g., type dropdown with 2 items)
    'options' => null, // Collection or array of items
    
    // For dynamic DB queries (e.g., items with 1000+ records)
    'modelClass' => null, // Model class (e.g., \App\Models\Item::class)
    'filterColumn' => null, // Optional: column to filter on (e.g., 'id')
    'filterOperator' => '!=', // Optional: operator for filter (e.g., '!=', 'NOT IN')
    'filterValue' => null, // Optional: value(s) to filter
])

{{-- Pure Livewire solution: Single component handles both static and dynamic data --}}
@if($label)
    <label class="block text-sm font-medium text-gray-700 mb-1">
        {{ $label }}@if($required)<span class="text-red-500">*</span>@endif
    </label>
@endif

@livewire('searchable-select', [
    'selectedId' => old($name, $value),
    'name' => $name,
    'staticOptions' => $options,
    'modelClass' => $modelClass,
    'displayField' => $displayField,
    'valueField' => $valueField,
    'placeholder' => $placeholder,
    'searchPlaceholder' => $searchPlaceholder,
    'entity' => $entity,
    'required' => $required,
    'filterColumn' => $filterColumn,
    'filterOperator' => $filterOperator,
    'filterValue' => $filterValue,
])
