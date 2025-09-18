@props([
    'view' => null,            // string URL or null
    'edit' => null,            // string URL or null
    'delete' => null,          // string URL or null (form action)
    'deleteConfirm' => 'Delete this record?',
    'deleteLabel' => 'Delete',
    'entity' => null,          // optional entity key for color variant
    'recordId' => null,        // unique identifier for this record
    'recordName' => null,      // display name for this record
])
@php
    $editBase = 'inline-flex items-center px-2.5 py-1.5 rounded-md border text-xs font-medium bg-white';
    $editClasses = match($entity) {
        'items' => $editBase.' border-teal-200 text-teal-600 hover:text-teal-700 hover:bg-teal-50',
        'partners' => $editBase.' border-yellow-200 text-yellow-600 hover:text-yellow-700 hover:bg-yellow-50',
        default => $editBase.' border-indigo-200 text-indigo-600 hover:text-indigo-700 hover:bg-indigo-50',
    };
@endphp

<div class="flex items-center justify-end gap-2">
    @if($view)
        <a href="{{ $view }}" class="inline-flex items-center px-2.5 py-1.5 rounded-md border border-gray-200 bg-white text-gray-600 hover:text-gray-800 hover:bg-gray-50 text-xs font-medium">
            <x-heroicon-o-eye class="w-4 h-4 mr-1" />
            <span>View</span>
        </a>
    @endif

    @if($edit)
        <a href="{{ $edit }}" class="{{ $editClasses }}">
            <x-heroicon-o-pencil-square class="w-4 h-4 mr-1" />
            <span>Edit</span>
        </a>
    @endif

    @if($delete)
        <x-table.delete-button 
            :id="$entity . '-' . $recordId"
            :entity="$entity ?: 'item'"
            :name="$recordName ?: ''"
            :action="$delete"
            :label="$deleteLabel" />
    @endif
</div>
