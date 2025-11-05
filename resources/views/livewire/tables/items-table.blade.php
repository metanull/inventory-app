<div class="space-y-4">
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
                <x-table.sortable-header field="created_at" label="Created" :sort-by="$sortBy" :sort-direction="$sortDirection" class="hidden lg:table-cell" />
                <x-table.sortable-header field="updated_at" label="Updated" :sort-by="$sortBy" :sort-direction="$sortDirection" class="hidden lg:table-cell" />
                <x-table.header-cell hidden="hidden sm:table-cell">
                    <span class="sr-only">Actions</span>
                </x-table.header-cell>
            </x-table.header>
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
