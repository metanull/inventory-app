@props([
    'name' => '',
    'label' => '',
    'selectedOptions' => null, // Pre-loaded Eloquent collection of currently selected rows (bounded by selection size)
    'displayField' => 'internal_name',
    'placeholder' => 'Select...',
    'searchPlaceholder' => 'Type to search...',
    'entity' => null, // For entity color theming

    // For static options (bounded enums only — not growable entities)
    'options' => null,

    // For dynamic DB queries (growable entities)
    'modelClass' => null,
    'scopes' => null,
    'perPage' => null,
    'filterColumn' => null,
    'filterOperator' => '!=',
    'filterValue' => null,
])

{{-- Derive the initial selectedIds from the pre-loaded collection (safe: bounded by selection count) --}}
@php
    $initialIds = $selectedOptions
        ? collect($selectedOptions)->map(fn ($o) => (string) (is_object($o) ? $o->id : $o['id']))->all()
        : [];

    $livewireProps = array_merge([
        'selectedIds' => $initialIds,
        'name' => $name,
        'staticOptions' => $options,
        'modelClass' => $modelClass,
        'displayField' => $displayField,
        'placeholder' => $placeholder,
        'searchPlaceholder' => $searchPlaceholder,
        'entity' => $entity,
        'scopes' => $scopes,
        'filterColumn' => $filterColumn,
        'filterOperator' => $filterOperator,
        'filterValue' => $filterValue,
    ], $perPage !== null ? ['perPage' => $perPage] : []);
@endphp

@if($label)
    <label class="block text-sm font-medium text-gray-700 mb-1">
        {{ $label }}
    </label>
@endif

@livewire('searchable-multi-select', $livewireProps)
