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
        <button wire:click="toggleHierarchyMode" type="button" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md text-sm font-medium border {{ $hierarchyMode ? 'bg-teal-50 border-teal-300 text-teal-800' : 'bg-gray-50 border-gray-300 text-gray-700' }} hover:bg-gray-100">
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
            <button wire:click="$set('parentId', '')" type="button" class="hover:text-gray-700">All Items</button>
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

    <x-table.filter-bar wireModel="q" placeholder="Search internal name...">
        <!-- Type Filter -->
        <div class="flex items-center gap-2">
            <label for="typeFilter" class="text-sm text-gray-700">Type:</label>
            <select wire:model.live="typeFilter" id="typeFilter" class="rounded-md border-gray-300 {{ $c['focus'] ?? '' }} text-sm">
                <option value="">All Types</option>
                <option value="object">Object</option>
                <option value="monument">Monument</option>
                <option value="detail">Detail</option>
                <option value="picture">Picture</option>
            </select>
        </div>

        <!-- Tag Filter with Multiselect Component -->
        <x-form.multiselect
            label="Filter by tags:"
            placeholder="Type to search tags..."
            :options="$availableTags"
            displayField="internal_name"
            descriptionField="description"
            wireModel="selectedTags"
            chipColor="violet"
        />
    </x-table.filter-bar>

    <!-- Selected Tags Display -->
    @if(!empty($selectedTags))
        <x-ui.chips
            :items="$availableTags->whereIn('id', $selectedTags)"
            displayField="internal_name"
            wireRemove="removeTag"
            color="violet"
        />
    @endif

    <div class="bg-white shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <x-table.header>
                <x-table.sortable-header field="internal_name" label="Internal Name" :sort-by="$sortBy" :sort-direction="$sortDirection" />
                <x-table.header-cell hidden="hidden md:table-cell">Backward Compatibility</x-table.header-cell>
                @if($hierarchyMode)
                    <x-table.header-cell hidden="hidden md:table-cell">Children</x-table.header-cell>
                @endif
                <x-table.sortable-header field="created_at" label="Created" :sort-by="$sortBy" :sort-direction="$sortDirection" class="hidden lg:table-cell" />
                <x-table.sortable-header field="updated_at" label="Updated" :sort-by="$sortBy" :sort-direction="$sortDirection" class="hidden lg:table-cell" />
                <x-table.header-cell hidden="hidden sm:table-cell">
                    <span class="sr-only">Actions</span>
                </x-table.header-cell>
            </x-table.header>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($items as $item)
                    <tr class="hover:bg-gray-50 cursor-pointer" wire:key="item-{{ $item->id }}" onclick="window.location='{{ route('items.show', $item) }}'">
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">
                            <div class="flex items-center gap-2">
                                {{ $item->internal_name }}
                                @if($hierarchyMode && $item->children_count > 0)
                                    <button 
                                        wire:click.stop="navigateToParent('{{ $item->id }}')" 
                                        type="button"
                                        class="inline-flex items-center text-teal-600 hover:text-teal-800"
                                        title="Browse children"
                                    >
                                        <x-heroicon-o-chevron-right class="w-4 h-4" />
                                    </button>
                                @endif
                            </div>
                        </td>
                        <td class="hidden md:table-cell px-4 py-3 text-sm text-gray-500">{{ $item->backward_compatibility ?? '—' }}</td>
                        @if($hierarchyMode)
                            <td class="hidden md:table-cell px-4 py-3 text-sm text-gray-500">
                                @if($item->children_count > 0)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-teal-100 text-teal-800">
                                        {{ $item->children_count }}
                                    </span>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                        @endif
                        <td class="hidden lg:table-cell px-4 py-3 text-xs text-gray-400">{{ optional($item->created_at)->format('Y-m-d H:i') }}</td>
                        <td class="hidden lg:table-cell px-4 py-3 text-xs text-gray-400">{{ optional($item->updated_at)->format('Y-m-d H:i') }}</td>
                        <td class="hidden sm:table-cell px-4 py-3 text-right text-sm" onclick="event.stopPropagation()">
                            <x-table.row-actions
                                :view="route('items.show', $item)"
                                :edit="route('items.edit', $item)"
                                :delete="route('items.destroy', $item)"
                                delete-confirm="Delete this item?"
                                entity="items"
                                :record-id="$item->id"
                                :record-name="$item->internal_name"
                            />
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $hierarchyMode ? 6 : 5 }}" class="px-4 py-8 text-center text-sm text-gray-500">No items found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>
        <x-layout.pagination 
            :paginator="$items" 
            entity="items"
            param-page="page"
        />
    </div>
    
    <!-- Delete confirmation modal -->
    <x-table.delete-modal />
</div>
