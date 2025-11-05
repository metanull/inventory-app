{{--
    Quick Actions Sidebar Card
    Displays edit, delete, and custom actions
--}}

@props([
    'entity' => '',
    'editRoute' => null,
    'deleteRoute' => null,
    'deleteConfirm' => 'Are you sure?',
])

@php($c = $entityColor($entity))

<x-sidebar.card title="Quick Actions" icon="bolt">
    <div class="space-y-2">
        @if($editRoute)
            @can(\App\Enums\Permission::UPDATE_DATA->value)
                <a href="{{ $editRoute }}" class="flex items-center gap-2 w-full px-3 py-2 rounded-md text-sm font-medium {{ $c['button'] }} text-white hover:opacity-90 transition">
                    <x-heroicon-o-pencil class="w-4 h-4" />
                    Edit
                </a>
            @endcan
        @endif

        @if($deleteRoute)
            @can(\App\Enums\Permission::DELETE_DATA->value)
                <button type="button"
                        onclick="openModal('delete-{{ $entity }}-modal')"
                        class="flex items-center gap-2 w-full px-3 py-2 rounded-md text-sm font-medium bg-red-600 hover:bg-red-700 text-white transition">
                    <x-heroicon-o-trash class="w-4 h-4" />
                    Delete
                </button>
            @endcan
        @endif

        {{ $slot }}
    </div>
</x-sidebar.card>
