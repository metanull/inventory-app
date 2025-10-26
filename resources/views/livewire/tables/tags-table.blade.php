<div class="space-y-4">
    <x-table.filter-bar wireModel="q" placeholder="Search tags..." />

    <div class="bg-white shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <x-table.sortable-header field="internal_name" label="Internal Name" :sort-by="$sortBy" :sort-direction="$sortDirection" />
                    <th class="hidden md:table-cell px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Description
                    </th>
                    <th class="hidden md:table-cell px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Items
                    </th>
                    <th class="hidden lg:table-cell px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Legacy ID
                    </th>
                    <x-table.sortable-header field="created_at" label="Created" :sort-by="$sortBy" :sort-direction="$sortDirection" class="hidden lg:table-cell" />
                    <th class="hidden sm:table-cell px-4 py-3">
                        <span class="sr-only">Actions</span>
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($tags as $tag)
                    <tr class="hover:bg-gray-50 cursor-pointer" wire:key="tag-{{ $tag->id }}" onclick="window.location='{{ route('tags.show', $tag) }}'">
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $tag->internal_name }}</td>
                        <td class="hidden md:table-cell px-4 py-3 text-sm text-gray-500">{{ Str::limit($tag->description, 100) }}</td>
                        <td class="hidden md:table-cell px-4 py-3 text-sm" onclick="event.stopPropagation()">
                            @php($itemCount = $tag->items()->count())
                            @if($itemCount > 0)
                                <a href="{{ route('items.index', ['selectedTags' => [$tag->id]]) }}" 
                                   class="inline-flex items-center text-blue-600 hover:text-blue-800"
                                   title="View items with this tag">
                                    {{ $itemCount }} {{ \Illuminate\Support\Str::plural('item', $itemCount) }}
                                    <x-heroicon-o-arrow-top-right-on-square class="w-4 h-4 ml-1" />
                                </a>
                            @else
                                <span class="text-gray-400">0 items</span>
                            @endif
                        </td>
                        <td class="hidden lg:table-cell px-4 py-3 text-sm text-gray-500">{{ $tag->backward_compatibility ?? '—' }}</td>
                        <td class="hidden lg:table-cell px-4 py-3 text-xs text-gray-400">{{ optional($tag->created_at)->format('Y-m-d H:i') }}</td>
                        <td class="hidden sm:table-cell px-4 py-3 text-right text-sm" onclick="event.stopPropagation()">
                            <x-table.row-actions
                                :view="route('tags.show', $tag)"
                                :edit="route('tags.edit', $tag)"
                                :delete="route('tags.destroy', $tag)"
                                delete-confirm="Delete this tag?"
                                entity="tag"
                                :record-id="$tag->id"
                                :record-name="$tag->internal_name"
                            />
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500">No tags found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>
        <x-layout.pagination 
            :paginator="$tags" 
            entity="tag"
            param-page="page"
        />
    </div>
    
    <!-- Delete confirmation modal -->
    <x-table.delete-modal />
</div>
