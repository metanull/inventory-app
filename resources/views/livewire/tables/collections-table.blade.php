<div class="space-y-4">
    <!-- Hierarchy controls -->
    @if($hierarchyMode)
        <div class="flex items-center gap-2 flex-wrap">
            @if($parentId !== '')
                <button wire:click="navigateUp" class="inline-flex items-center gap-1 px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                    <x-heroicon-o-arrow-left class="w-4 h-4" />
                    Back
                </button>
                <nav class="flex items-center gap-1 text-sm text-gray-500">
                    <button wire:click="navigateToParent('')" class="hover:text-gray-700">All</button>
                    @foreach($breadcrumbs as $crumb)
                        <span>/</span>
                        @if(!$loop->last)
                            <button wire:click="navigateToParent('{{ $crumb->id }}')" class="hover:text-gray-700">{{ $crumb->internal_name }}</button>
                        @else
                            <span class="font-medium text-gray-900">{{ $crumb->internal_name }}</span>
                        @endif
                    @endforeach
                </nav>
            @else
                <span class="text-sm font-medium text-gray-700">Root collections</span>
            @endif
            <button wire:click="toggleHierarchyMode" class="ml-auto inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-gray-500 bg-white border border-gray-200 rounded-md hover:bg-gray-50">
                <x-heroicon-o-list-bullet class="w-3.5 h-3.5" />
                Show all (flat)
            </button>
        </div>
    @else
        <div class="flex items-center justify-between">
            <span class="text-sm text-gray-500">Showing all collections</span>
            <button wire:click="toggleHierarchyMode" class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-gray-500 bg-white border border-gray-200 rounded-md hover:bg-gray-50">
                <x-heroicon-o-folder-open class="w-3.5 h-3.5" />
                Hierarchy view
            </button>
        </div>
    @endif

    <x-table.filter-bar wireModel="q" placeholder="Search internal name..." />

    <div class="bg-white shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <x-table.header>
                <x-table.sortable-header field="internal_name" label="Internal Name" :sort-by="$sortBy" :sort-direction="$sortDirection" />
                <x-table.header-cell hidden="hidden md:table-cell">Language</x-table.header-cell>
                <x-table.header-cell hidden="hidden lg:table-cell">Context</x-table.header-cell>
                <x-table.sortable-header field="display_order" label="Order" :sort-by="$sortBy" :sort-direction="$sortDirection" class="hidden lg:table-cell" />
                <x-table.sortable-header field="created_at" label="Created" :sort-by="$sortBy" :sort-direction="$sortDirection" class="hidden lg:table-cell" />
                @if($hierarchyMode)
                    <x-table.header-cell hidden="hidden sm:table-cell">Children</x-table.header-cell>
                @endif
                <x-table.header-cell hidden="hidden sm:table-cell">
                    <span class="sr-only">Actions</span>
                </x-table.header-cell>
            </x-table.header>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($collections as $collection)
                    @php($hasChildren = $hierarchyMode && $collection->children_count > 0)
                    <tr class="{{ $hasChildren ? 'hover:bg-blue-50 cursor-pointer' : 'hover:bg-gray-50 cursor-pointer' }}"
                        wire:key="collection-{{ $collection->id }}"
                        @if($hasChildren)
                            wire:click="navigateToParent('{{ $collection->id }}')"
                        @else
                            onclick="window.location='{{ route('collections.show', $collection) }}'"
                        @endif
                    >
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">
                            <div class="flex items-center gap-1">
                                @if($hasChildren)
                                    <x-heroicon-o-folder class="w-4 h-4 text-blue-500 shrink-0" />
                                @endif
                                {{ $collection->internal_name }}
                            </div>
                        </td>
                        <td class="hidden md:table-cell px-4 py-3 text-sm text-gray-500">{{ $collection->language->internal_name ?? $collection->language_id }}</td>
                        <td class="hidden lg:table-cell px-4 py-3 text-sm text-gray-500">{{ $collection->context->internal_name ?? '—' }}</td>
                        <td class="hidden lg:table-cell px-4 py-3 text-sm text-gray-500">{{ $collection->display_order ?? '—' }}</td>
                        <td class="hidden lg:table-cell px-4 py-3 text-xs text-gray-400">{{ optional($collection->created_at)->format('Y-m-d H:i') }}</td>
                        @if($hierarchyMode)
                            <td class="hidden sm:table-cell px-4 py-3 text-sm text-gray-500">
                                @if($collection->children_count > 0)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">{{ $collection->children_count }}</span>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                        @endif
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
