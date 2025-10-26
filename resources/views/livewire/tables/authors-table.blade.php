<div class="space-y-4">
    <x-table.filter-bar wireModel="q" placeholder="Search authors..." />

    <div class="bg-white shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <x-table.sortable-header field="name" label="Name" :sort-by="$sortBy" :sort-direction="$sortDirection" />
                    <th class="hidden md:table-cell px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Internal Name
                    </th>
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
                    <th class="hidden sm:table-cell px-4 py-3">
                        <span class="sr-only">Actions</span>
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($authors as $author)
                    <tr class="hover:bg-gray-50 cursor-pointer" wire:key="author-{{ $author->id }}" onclick="window.location='{{ route('authors.show', $author) }}'">
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $author->name }}</td>
                        <td class="hidden md:table-cell px-4 py-3 text-sm text-gray-500">{{ $author->internal_name ?? '—' }}</td>
                        <td class="hidden lg:table-cell px-4 py-3 text-xs text-gray-400">{{ optional($author->created_at)->format('Y-m-d H:i') }}</td>
                        <td class="hidden sm:table-cell px-4 py-3 text-right text-sm" onclick="event.stopPropagation()">
                            <x-table.row-actions
                                :view="route('authors.show', $author)"
                                :edit="route('authors.edit', $author)"
                                :delete="route('authors.destroy', $author)"
                                delete-confirm="Delete this author?"
                                entity="author"
                                :record-id="$author->id"
                                :record-name="$author->name"
                            />
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-8 text-center text-sm text-gray-500">No authors found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>
        <x-layout.pagination 
            :paginator="$authors" 
            entity="author"
            param-page="page"
        />
    </div>
    
    <!-- Delete confirmation modal -->
    <x-table.delete-modal />
</div>
