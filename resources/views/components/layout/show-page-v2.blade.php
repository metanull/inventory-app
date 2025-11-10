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

<div class="min-h-screen bg-gray-50">
    <div class="bg-white border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-6">
            <div class="flex items-center justify-between gap-4">
                <div class="flex-1">
                    @if($backRoute)
                        <a href="{{ $backRoute }}" class="text-sm {{ $c['accentLink'] }} mb-4 inline-block">&larr; Back to list</a>
                    @endif
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
                        
                        @foreach($badges as $badge)
                            <x-display.badge :entity="$entity">{{ $badge }}</x-display.badge>
                        @endforeach
                    </x-entity.header>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @if(session('status'))
            <x-ui.alert :message="session('status')" type="success" :entity="$entity" />
        @endif
        <div class="grid gap-8" style="grid-template-columns: 1fr; grid-template-areas: 'main' 'sidebar';">
            <div class="space-y-6" style="grid-area: main;">
                {{ $slot }}
            </div>
            <div style="grid-area: sidebar;">
                <div class="space-y-6">
                    {{ $sidebar ?? '' }}
                </div>
            </div>
        </div>
    </div>
    
    <style>
        @media (min-width: 1024px) {
            .max-w-7xl > .grid {
                grid-template-columns: 3fr 1fr !important;
                grid-template-areas: 'main sidebar' !important;
            }
            .max-w-7xl > .grid > div[style*="sidebar"] > div {
                position: sticky;
                top: 2rem;
            }
        }
    </style>

    @if($deleteRoute)
        <x-ui.delete-modal 
            :id="'delete-' . $entity . '-modal'"
            :entity="$entity"
            :name="$title"
            :action="$deleteRoute" />
    @endif
</div>
