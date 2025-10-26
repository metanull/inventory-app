@props([
    'items',                    // Collection of items to render as checkboxes
    'name',                     // Input name (e.g., 'permissions[]')
    'labelField' => 'name',     // Field to use for checkbox label
    'valueField' => 'id',       // Field to use for checkbox value
    'descriptionField' => 'description', // Field to use for description text
    'selected' => [],           // Array of pre-selected values
    'entity' => null,           // Entity for color theming
    'columns' => '1 md:grid-cols-2', // Grid column layout
])

@php
    $c = $entity ? $entityColor($entity) : ['name' => 'indigo'];
    $nameWithoutBrackets = rtrim($name, '[]');
    $selectedValues = is_array($selected) ? $selected : [$selected];
    $oldValues = old($nameWithoutBrackets, $selectedValues);
    $oldValues = is_array($oldValues) ? $oldValues : [$oldValues];
@endphp

<div class="grid grid-cols-{{ $columns }} gap-4">
    @foreach($items as $item)
        @php
            $itemValue = is_object($item) ? $item->{$valueField} : $item[$valueField];
            $itemLabel = is_object($item) ? $item->{$labelField} : $item[$labelField];
            $itemDescription = is_object($item) && isset($item->{$descriptionField}) ? $item->{$descriptionField} : (is_array($item) && isset($item[$descriptionField]) ? $item[$descriptionField] : null);
            $isChecked = in_array($itemValue, $oldValues);
        @endphp
        
        <label class="flex items-start">
            <input type="checkbox" 
                   name="{{ $name }}" 
                   value="{{ $itemValue }}"
                   {{ $isChecked ? 'checked' : '' }}
                   class="mt-1 rounded border-gray-300 text-{{ $c['name'] }}-600 shadow-sm focus:border-{{ $c['name'] }}-300 focus:ring focus:ring-{{ $c['name'] }}-200 focus:ring-opacity-50">
            <div class="ml-3">
                <span class="text-sm font-medium text-gray-700">{{ $itemLabel }}</span>
                @if($itemDescription)
                    <p class="text-xs text-gray-500 mt-1">{{ $itemDescription }}</p>
                @endif
            </div>
        </label>
    @endforeach
</div>
