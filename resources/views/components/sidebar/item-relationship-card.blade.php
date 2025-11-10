{{--
    Unified Item Relationship Card Component
    Reusable for parent items, children items, and links
    
    Props:
    - title: Card title (e.g., "Parent Item", "Children", "Links")
    - model: The parent item model
    - items: Collection of related items to display
    - type: 'parent' | 'children' | 'links' (affects routes, labels, etc)
    - addRoute: Route name for adding items
    - removeRoute: Route name for removing items
    - canAdd: Whether to show add button
    - canRemove: Whether to show remove buttons
    - count: Optional count to display in title
--}}

@props([
    'title' => '',
    'model',
    'items' => collect(),
    'type' => 'children',  // 'parent', 'children', or 'links'
    'addRoute' => null,
    'removeRoute' => null,
    'canAdd' => true,
    'canRemove' => true,
    'count' => null,
    'direction' => null,  // 'incoming' or 'outgoing' for links
])

@php($tc = $entityColor('items'))

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
                    onclick="document.getElementById('add-item-form-{{ $type }}').classList.toggle('hidden')"
                    class="inline-flex items-center px-2 py-1 rounded text-xs {{ $tc['button'] }}">
                    <x-heroicon-o-plus class="w-3 h-3" />
                </button>
            @endcan
        @endif
    </div>

    <!-- Items List -->
    @if($items->isEmpty())
        <p class="text-xs text-gray-500 italic mb-3">No {{ strtolower($title) }}</p>
    @else
        <div class="space-y-2 mb-3">
            @foreach($items as $item)
                <div class="flex items-start gap-2 p-2 rounded hover:bg-gray-50 transition-colors group">
                    <!-- Type Icon -->
                    <div class="w-8 h-8 rounded bg-gray-100 flex items-center justify-center shrink-0">
                        <x-display.item-type-icon :type="$item->type" class="w-4 h-4 text-gray-600" />
                    </div>
                    
                    <!-- Content -->
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-1">
                            @if(isset($item->direction))
                                @if($item->direction === 'outgoing')
                                    <span class="text-blue-600 font-bold text-sm shrink-0" title="Outgoing link">»</span>
                                @else
                                    <span class="text-green-600 font-bold text-sm shrink-0" title="Incoming link">«</span>
                                @endif
                            @endif
                            <a href="{{ route('items.show', isset($item->direction) ? $item->item : $item) }}" 
                               class="text-xs font-medium {{ $tc['accentLink'] }} hover:underline truncate block">
                                {{ isset($item->direction) ? $item->item->internal_name : $item->internal_name }}
                            </a>
                        </div>
                        <p class="text-xs text-gray-500 font-mono">
                            <x-format.uuid :uuid="isset($item->direction) ? $item->item->id : $item->id" format="long" />
                        </p>
                    </div>

                    <!-- Remove Button -->
                    @if($canRemove && $removeRoute)
                        @can(\App\Enums\Permission::UPDATE_DATA->value)
                            <button 
                                type="button"
                                onclick="if(confirm('Remove {{ strtolower($title) }}?')) { document.getElementById('remove-item-form-{{ $type }}-{{ $item->id }}').submit(); }"
                                class="text-gray-400 hover:text-red-600 transition-colors opacity-0 group-hover:opacity-100 shrink-0">
                                <x-heroicon-o-x-mark class="w-3 h-3" />
                            </button>
                            <form id="remove-item-form-{{ $type }}-{{ $item->id }}" action="{{ $removeRoute }}" method="POST" class="hidden">
                                @csrf
                                @method('DELETE')
                            </form>
                        @endcan
                    @endif
                </div>
            @endforeach
        </div>
    @endif

    <!-- Add Item Form (Hidden by default) -->
    @if($canAdd && $addRoute)
        @can(\App\Enums\Permission::UPDATE_DATA->value)
            <div id="add-item-form-{{ $type }}" class="{{ $items->isNotEmpty() ? 'pt-3 border-t border-gray-100' : '' }} hidden">
                <form action="{{ $addRoute }}" method="POST" class="space-y-2">
                    @csrf
                    <div>
                        {{ $slot }}
                    </div>
                    <div class="flex gap-2">
                        <button 
                            type="submit"
                            class="inline-flex items-center px-2 py-1 rounded text-xs {{ $tc['button'] }}">
                            Add
                        </button>
                        <button 
                            type="button"
                            onclick="document.getElementById('add-item-form-{{ $type }}').classList.add('hidden')"
                            class="inline-flex items-center px-2 py-1 rounded text-xs border border-gray-300 bg-white hover:bg-gray-50 text-gray-700">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        @endcan
    @endif
</div>
