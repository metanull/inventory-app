@props([
    'id',
    'entity',
    'name',
    'action',
    'label' => 'Delete'
])

<button type="button" 
        onclick="openTableDeleteModal('{{ $id }}', '{{ $entity }}', '{{ $name }}', '{{ $action }}')" 
        class="inline-flex items-center text-red-600 hover:text-red-900 text-sm font-medium p-1 rounded hover:bg-red-50 transition-colors" 
        title="{{ $label }}">
    <x-heroicon-o-trash class="h-4 w-4" />
</button>