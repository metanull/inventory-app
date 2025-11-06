{{--
    Show Page with Sidebar Layout
    Modern center content + right sidebar architecture
    
    Usage:
    <x-layout.show-page-with-sidebar 
        entity="items"
        :title="$item->internal_name"
        :back-route="route('items.index')"
        :edit-route="route('items.edit', $item)"
        :delete-route="route('items.destroy', $item)"
    >
        <!-- Center content goes here -->
        <x-display.description-list>...</x-display.description-list>
        
        <!-- Sidebar goes here -->
        <x-slot name="sidebar">
            <x-sidebar.quick-actions :entity="$entity" :edit-route="$editRoute" :delete-route="$deleteRoute" />
            <x-sidebar.navigation :back-route="$backRoute" />
            <x-sidebar.related-counts :model="$item" :entity="$entity" />
            <x-sidebar.system-properties :id="$item->id" :backward-compatibility-id="$item->backward_compatibility" :created-at="$item->created_at" :updated-at="$item->updated_at" />
        </x-slot>
    </x-layout.show-page-with-sidebar>
--}}

@props([
    'entity' => '',
    'title' => '',
    'backRoute' => '',
    'editRoute' => '',
    'deleteRoute' => '',
    'deleteConfirm' => 'Are you sure you want to delete this record?',
    'backwardCompatibility' => null,
    'badges' => [],
])

@php($c = $entityColor($entity))

<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    @if($backRoute)
        <div class="mb-4">
            <a href="{{ $backRoute }}" class="text-sm {{ $c['accentLink'] }}">&larr; Back to list</a>
        </div>
    @endif

    {{-- Header (full width) --}}
    <x-entity.header :entity="$entity" :title="$title">
        @if($editRoute)
            @can(\App\Enums\Permission::UPDATE_DATA->value)
                <a href="{{ $editRoute }}" class="inline-flex items-center px-3 py-2 rounded-md {{ $c['button'] }} text-sm font-medium">Edit</a>
            @endcan
        @endif

        @if($deleteRoute)
            @can(\App\Enums\Permission::DELETE_DATA->value)
                <button type="button"
                        onclick="openModal('delete-{{ $entity }}-modal')"
                        class="inline-flex items-center px-3 py-2 rounded-md bg-red-600 hover:bg-red-700 text-white text-sm font-medium">
                    Delete
                </button>
            @endcan
        @endif

        @if($backwardCompatibility)
            <x-display.badge :entity="$entity">Legacy: {{ $backwardCompatibility }}</x-display.badge>
        @endif

        @foreach($badges as $badge)
            <x-display.badge :entity="$entity">{{ $badge }}</x-display.badge>
        @endforeach
    </x-entity.header>

    {{-- Grid Layout: Content + Sidebar --}}
    <div class="mt-6 grid grid-cols-1 lg:grid-cols-[1fr_320px] gap-6">
        {{-- CENTER CONTENT --}}
        <div class="space-y-6 min-w-0">
            {{ $slot }}
        </div>

        {{-- RIGHT SIDEBAR --}}
        <aside class="space-y-4 lg:sticky lg:top-6 lg:self-start h-fit">
            {{ $sidebar ?? '' }}
        </aside>
    </div>

    {{-- Delete Modal --}}
    @if($deleteRoute)
        <x-ui.delete-modal
            :id="'delete-' . $entity . '-modal'"
            :entity="$entity"
            :name="$title"
            :action="$deleteRoute" />
    @endif
</div>
