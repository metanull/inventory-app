<div class="space-y-4">
    {{-- Hierarchy / Flat toggle --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-2">
            @if($hierarchyMode && $parentId)
                <button wire:click="navigateUp" type="button" class="inline-flex items-center gap-1 text-sm text-gray-600 hover:text-gray-900">
                    <x-heroicon-o-arrow-left class="w-4 h-4" />
                    Back
                </button>
            @endif
        </div>
        <button wire:click="toggleHierarchyMode" type="button" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md text-sm font-medium border {{ $hierarchyMode ? 'bg-yellow-50 border-yellow-300 text-yellow-800' : 'bg-gray-50 border-gray-300 text-gray-700' }} hover:bg-gray-100">
            @if($hierarchyMode)
                <x-heroicon-o-queue-list class="w-4 h-4" />
                Hierarchy
            @else
                <x-heroicon-o-bars-3 class="w-4 h-4" />
                Flat
            @endif
        </button>
    </div>

    {{-- Breadcrumbs --}}
    @if($hierarchyMode && !empty($breadcrumbs))
        <nav class="flex items-center text-sm text-gray-500" aria-label="Breadcrumb">
            <button wire:click="$set('parentId', '')" type="button" class="hover:text-gray-700">All Collections</button>
            @foreach($breadcrumbs as $crumb)
                <x-heroicon-o-chevron-right class="w-4 h-4 mx-1 shrink-0" />
                @if($loop->last)
                    <span class="font-medium text-gray-900">{{ $crumb->internal_name }}</span>
                @else
                    <button wire:click="navigateToParent('{{ $crumb->id }}')" type="button" class="hover:text-gray-700">{{ $crumb->internal_name }}</button>
                @endif
            @endforeach
        </nav>
    @endif

    <x-table.filter-bar wireModel="q" placeholder="Search internal name..." />

    <div class="bg-white shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <x-table.header>
                <x-table.sortable-header field="internal_name" label="Internal Name" :sort-by="$sortBy" :sort-direction="$sortDirection" />
                <x-table.header-cell hidden="hidden md:table-cell">Language</x-table.header-cell>
                <x-table.header-cell hidden="hidden lg:table-cell">Context</x-table.header-cell>
                @if($hierarchyMode)
                    <x-table.header-cell hidden="hidden md:table-cell">Children</x-table.header-cell>
                @endif
                <x-table.sortable-header field="display_order" label="Order" :sort-by="$sortBy" :sort-direction="$sortDirection" class="hidden lg:table-cell" />
                <x-table.sortable-header field="created_at" label="Created" :sort-by="$sortBy" :sort-direction="$sortDirection" class="hidden lg:table-cell" />
                <x-table.header-cell hidden="hidden sm:table-cell">
                    <span class="sr-only">Actions</span>
                </x-table.header-cell>
            </x-table.header>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($collections as $collection)
                    <tr class="hover:bg-gray-50 cursor-pointer" wire:key="collection-{{ $collection->id }}" onclick="window.location='{{ route('collections.show', $collection) }}'">
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">
                            <div class="flex items-center gap-2">
                                {{ $collection->internal_name }}
                                @if($hierarchyMode && $collection->children_count > 0)
                                    <button 
                                        wire:click.stop="navigateToParent('{{ $collection->id }}')" 
                                        type="button"
                                        class="inline-flex items-center text-yellow-600 hover:text-yellow-800"
                                        title="Browse children"
                                    >
                                        <x-heroicon-o-chevron-right class="w-4 h-4" />
                                    </button>
                                @endif
                            </div>
                        </td>
                        <td class="hidden md:table-cell px-4 py-3 text-sm text-gray-500">{{ $collection->language->internal_name ?? $collection->language_id }}</td>
                        <td class="hidden lg:table-cell px-4 py-3 text-sm text-gray-500">{{ $collection->context->internal_name ?? '—' }}</td>
                        @if($hierarchyMode)
                            <td class="hidden md:table-cell px-4 py-3 text-sm text-gray-500">
                                @if($collection->children_count > 0)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        {{ $collection->children_count }}
                                    </span>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                        @endif
                        <td class="hidden lg:table-cell px-4 py-3 text-sm text-gray-500">{{ $collection->display_order ?? '—' }}</td>
                        <td class="hidden lg:table-cell px-4 py-3 text-xs text-gray-400">{{ optional($collection->created_at)->format('Y-m-d H:i') }}</td>
                        <td class="hidden sm:table-cell px-4 py-3 text-right text-sm" onclick="event.stopPropagation()">
                            <x-table.row-actions
                                :view="route('collections.show', $collection)"
                                :edit="route('collections.edit', $collection)"
                                :delete="route('collections.destroy', $collection)"
                                delete-confirm="Delete this collection?"
                                entity="collections"
                                :record-id="$collection->id"
                                :record-name="$collection->internal_name"
                            />
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $hierarchyMode ? 7 : 6 }}" class="px-4 py-8 text-center text-sm text-gray-500">No collections found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>
        <x-layout.pagination 
            :paginator="$collections" 
            entity="collections"
            param-page="page"
        />
    </div>
    
    <!-- Delete confirmation modal -->
    <x-table.delete-modal />
</div>
</div>
