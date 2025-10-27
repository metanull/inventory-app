<div class="space-y-4">
    <x-table.filter-bar wireModel="q" placeholder="Search internal name..." />

    <div class="bg-white shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <x-table.header>
                <x-table.sortable-header field="internal_name" label="Internal Name" :sort-by="$sortBy" :sort-direction="$sortDirection" />
                <x-table.header-cell hidden="hidden md:table-cell">Language</x-table.header-cell>
                <x-table.header-cell hidden="hidden lg:table-cell">Context</x-table.header-cell>
                <x-table.sortable-header field="created_at" label="Created" :sort-by="$sortBy" :sort-direction="$sortDirection" class="hidden lg:table-cell" />
                <x-table.header-cell hidden="hidden sm:table-cell">
                    <span class="sr-only">Actions</span>
                </x-table.header-cell>
            </x-table.header>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($collections as $collection)
                    <tr class="hover:bg-gray-50 cursor-pointer" wire:key="collection-{{ $collection->id }}" onclick="window.location='{{ route('collections.show', $collection) }}'">
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $collection->internal_name }}</td>
                        <td class="hidden md:table-cell px-4 py-3 text-sm text-gray-500">{{ $collection->language->internal_name ?? $collection->language_id }}</td>
                        <td class="hidden lg:table-cell px-4 py-3 text-sm text-gray-500">{{ $collection->context->internal_name ?? '—' }}</td>
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
                        <td colspan="5" class="px-4 py-8 text-center text-sm text-gray-500">No collections found.</td>
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
