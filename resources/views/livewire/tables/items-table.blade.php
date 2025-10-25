<div class="space-y-4">
    <x-table.filter-bar wireModel="q" placeholder="Search internal name...">
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
            <thead class="bg-gray-50">
                <tr>
                    <x-table.sortable-header field="internal_name" label="Internal Name" :sort-by="$sortBy" :sort-direction="$sortDirection" />
                    <th class="hidden md:table-cell px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Backward Compatibility</th>
                    <th class="hidden lg:table-cell px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <button wire:click="sortBy('created_at')" 
                                class="group flex items-center space-x-1 hover:text-gray-700 transition-colors duration-200">
                            <span>Created</span>
                            <span class="flex flex-col">
                                @if($sortBy === 'created_at')
                                    @if($sortDirection === 'asc')
                                        <x-heroicon-s-chevron-up class="w-3 h-3 text-gray-600" />
                                    @else
                                        <x-heroicon-s-chevron-down class="w-3 h-3 text-gray-600" />
                                    @endif
                                @else
                                    <x-heroicon-s-chevron-up class="w-3 h-3 text-gray-300 group-hover:text-gray-400" />
                                @endif
                            </span>
                        </button>
                    </th>
                    <th class="hidden lg:table-cell px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Updated</th>
                    <th class="hidden sm:table-cell px-4 py-3">
                        <span class="sr-only">Actions</span>
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($items as $item)
                    <tr class="hover:bg-gray-50 cursor-pointer" wire:key="item-{{ $item->id }}" onclick="window.location='{{ route('items.show', $item) }}'">
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $item->internal_name }}</td>
                        <td class="hidden md:table-cell px-4 py-3 text-sm text-gray-500">{{ $item->backward_compatibility ?? '—' }}</td>
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
                        <td colspan="5" class="px-4 py-8 text-center text-sm text-gray-500">No items found.</td>
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
