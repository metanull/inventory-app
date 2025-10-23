<div class="space-y-4">
    <div class="flex flex-wrap items-center gap-3">
        <div class="relative">
            <input wire:model.live.debounce.300ms="q" type="text" placeholder="Search internal name..." class="w-64 rounded-md border-gray-300 {{ $c['focus'] ?? '' }}" />
        </div>
        @if($q)
            <button wire:click="$set('q','')" type="button" class="text-sm text-gray-600 hover:underline">Clear</button>
        @endif

        <!-- Tag Filter with Autocomplete -->
        <div x-data="{ 
            open: false, 
            search: '', 
            tags: @js($availableTags),
            selectedIds: @entangle('selectedTags').live,
            get filteredTags() {
                if (this.search === '') return this.tags;
                const searchLower = this.search.toLowerCase();
                return this.tags.filter(tag => 
                    tag.internal_name.toLowerCase().includes(searchLower) &&
                    !this.selectedIds.includes(tag.id)
                );
            },
            addTag(tag) {
                if (!this.selectedIds.includes(tag.id)) {
                    const newIds = [...this.selectedIds, tag.id];
                    this.selectedIds = newIds;
                    this.$wire.set('selectedTags', newIds);
                }
                this.search = '';
                this.open = false;
            }
        }" class="relative flex items-center gap-2">
            <label for="tag-search" class="text-sm font-medium text-gray-700">Filter by tags:</label>
            <div class="relative w-64">
                <input 
                    id="tag-search"
                    x-model="search"
                    @focus="open = true"
                    @click.away="open = false"
                    @keydown.escape="open = false"
                    type="text" 
                    placeholder="Type to search tags..." 
                    class="w-full rounded-md border-gray-300 text-sm {{ $c['focus'] ?? '' }}" 
                />
                
                <!-- Dropdown -->
                <div x-show="open && filteredTags.length > 0" 
                     x-cloak
                     class="absolute z-10 mt-1 w-full bg-white shadow-lg max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm">
                    <template x-for="tag in filteredTags" :key="tag.id">
                        <div @click="addTag(tag)" 
                             class="cursor-pointer select-none relative py-2 px-3 hover:bg-violet-50">
                            <span class="block truncate" x-text="tag.internal_name"></span>
                            <span class="block text-xs text-gray-500 truncate" x-text="tag.description"></span>
                        </div>
                    </template>
                </div>
            </div>
            @if(!empty($selectedTags))
                <button wire:click="clearTags" type="button" class="text-sm text-gray-600 hover:underline">Clear all</button>
            @endif
        </div>
    </div>

    <!-- Selected Tags Display -->
    @if(!empty($selectedTags))
        <div class="flex flex-wrap gap-2">
            @foreach($selectedTags as $tagId)
                @php($tag = $availableTags->firstWhere('id', $tagId))
                @if($tag)
                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-sm bg-violet-100 text-violet-800">
                        {{ $tag->internal_name }}
                        <button wire:click="removeTag('{{ $tagId }}')" type="button" class="hover:text-violet-900">
                            <x-heroicon-s-x-mark class="w-4 h-4" />
                        </button>
                    </span>
                @endif
            @endforeach
        </div>
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
