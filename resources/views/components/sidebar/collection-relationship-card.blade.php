{{--
    Unified Collection Relationship Card Component
    Reusable for parent collections and children collections

    Props:
    - title: Card title (e.g., "Parent Collection", "Children")
    - model: The current collection model
    - collections: Collection of related collections to display
    - type: 'parent' | 'children' (affects routes, labels, etc)
    - addRoute: Route name for adding collections
    - removeRoute: Route name for removing collections
    - canAdd: Whether to show add button
    - canRemove: Whether to show remove buttons
    - count: Optional count to display in title
--}}

@props([
    'title' => '',
    'model',
    'collections' => collect(),
    'type' => 'children',
    'addRoute' => null,
    'removeRoute' => null,
    'canAdd' => true,
    'canRemove' => true,
    'count' => null,
])

@php($tc = $entityColor('collections'))

<div class="bg-white rounded-lg border border-gray-200 shadow-sm p-4">
    <!-- Header with Title and Add Button -->
    <div class="flex items-center justify-between gap-2 mb-3">
        <h3 class="text-sm font-semibold text-gray-900">
            {{ $title }}
            @if($count !== null && $count > 0)
                <span class="text-xs font-normal text-gray-500">({{ $count }})</span>
            @endif
        </h3>
        @if($canAdd && $addRoute)
            @can(\App\Enums\Permission::UPDATE_DATA->value)
                <button
                    type="button"
                    onclick="document.getElementById('add-collection-form-{{ $type }}').classList.toggle('hidden')"
                    class="inline-flex items-center px-2 py-1 rounded text-xs {{ $tc['button'] }}">
                    <x-heroicon-o-plus class="w-3 h-3" />
                </button>
            @endcan
        @endif
    </div>

    <!-- Collections List -->
    @if($collections->isEmpty())
        <p class="text-xs text-gray-500 italic mb-3">No {{ strtolower($title) }}</p>
    @else
        <div class="space-y-2 mb-3">
            @foreach($collections as $collection)
                <div class="flex items-start gap-2 p-2 rounded hover:bg-gray-50 transition-colors group">
                    <!-- Type Badge -->
                    <div class="w-8 h-8 rounded bg-gray-100 flex items-center justify-center shrink-0">
                        <x-heroicon-o-squares-2x2 class="w-4 h-4 text-gray-600" />
                    </div>

                    <!-- Content -->
                    <div class="min-w-0 flex-1">
                        <a href="{{ route('collections.show', $collection) }}"
                           class="text-xs font-medium {{ $tc['accentLink'] }} hover:underline truncate block">
                            {{ $collection->internal_name }}
                        </a>
                        <p class="text-xs text-gray-500">
                            {{ ucfirst($collection->type) }}
                        </p>
                    </div>

                    <!-- Remove Button -->
                    @if($canRemove && $removeRoute)
                        @can(\App\Enums\Permission::UPDATE_DATA->value)
                            <button
                                type="button"
                                onclick="if(confirm('Remove {{ strtolower($title) }}?')) { document.getElementById('remove-collection-form-{{ $type }}-{{ $collection->id }}').submit(); }"
                                class="text-gray-400 hover:text-red-600 transition-colors opacity-0 group-hover:opacity-100 shrink-0">
                                <x-heroicon-o-x-mark class="w-3 h-3" />
                            </button>
                            <form id="remove-collection-form-{{ $type }}-{{ $collection->id }}" action="{{ $removeRoute }}" method="POST" class="hidden">
                                @csrf
                                @method('DELETE')
                            </form>
                        @endcan
                    @endif
                </div>
            @endforeach
        </div>
    @endif

    <!-- Add Collection Form (Hidden by default) -->
    @if($canAdd && $addRoute)
        @can(\App\Enums\Permission::UPDATE_DATA->value)
            <div id="add-collection-form-{{ $type }}" class="{{ $collections->isNotEmpty() ? 'pt-3 border-t border-gray-100' : '' }} hidden">
                <form action="{{ $addRoute }}" method="POST" class="space-y-2">
                    @csrf
                    <div>
                        {{ $slot }}
                    </div>
                    <div class="flex gap-2">
                        <button
                            type="submit"
                            class="inline-flex items-center px-2 py-1 rounded text-xs {{ $tc['button'] }}">
                            Set
                        </button>
                        <button
                            type="button"
                            onclick="document.getElementById('add-collection-form-{{ $type }}').classList.add('hidden')"
                            class="inline-flex items-center px-2 py-1 rounded text-xs border border-gray-300 bg-white hover:bg-gray-50 text-gray-700">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        @endcan
    @endif
</div>
