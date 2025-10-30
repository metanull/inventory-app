<div class="space-y-4">
    <x-table.filter-bar wireModel="q" placeholder="Search by target item...">
        @if($contexts->count() > 0)
            <div class="flex items-center gap-2">
                <label for="contextFilter" class="text-sm text-gray-700">Context:</label>
                <select wire:model.live="contextFilter" id="contextFilter" class="rounded-md border-gray-300 text-sm">
                    <option value="">All Contexts</option>
                    @foreach($contexts as $context)
                        <option value="{{ $context->id }}">{{ $context->internal_name }}</option>
                    @endforeach
                </select>
            </div>
        @endif
    </x-table.filter-bar>

    <div class="bg-white shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <x-table.header>
                <x-table.sortable-header field="target_id" label="Target Item" :sort-by="$sortBy" :sort-direction="$sortDirection" />
                <x-table.header-cell hidden="hidden md:table-cell">Context</x-table.header-cell>
                <x-table.sortable-header field="created_at" label="Created" :sort-by="$sortBy" :sort-direction="$sortDirection" class="hidden lg:table-cell" />
                <x-table.header-cell hidden="hidden sm:table-cell">
                    <span class="sr-only">Actions</span>
                </x-table.header-cell>
            </x-table.header>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($links as $link)
                    <tr class="hover:bg-gray-50 cursor-pointer" wire:key="link-{{ $link->id }}" onclick="window.location='{{ route('item-links.show', [$link->source, $link]) }}'">
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">
                            {{ $link->target->internal_name }}
                        </td>
                        <td class="hidden md:table-cell px-4 py-3 text-sm text-gray-700">
                            {{ $link->context->internal_name }}
                        </td>
                        <td class="hidden lg:table-cell px-4 py-3 text-xs text-gray-400">{{ optional($link->created_at)->format('Y-m-d H:i') }}</td>
                        <td class="hidden sm:table-cell px-4 py-3 text-right text-sm" onclick="event.stopPropagation()">
                            <x-table.row-actions
                                :view="route('item-links.show', [$link->source, $link])"
                                :edit="route('item-links.edit', [$link->source, $link])"
                                :delete="route('item-links.destroy', [$link->source, $link])"
                                delete-confirm="Delete this link?"
                                entity="item-item-links"
                                :record-id="$link->id"
                                :record-name="'Link to ' . $link->target->internal_name"
                            />
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-8 text-center text-sm text-gray-500">No links found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>
        <x-layout.pagination 
            :paginator="$links" 
            entity="item-item-links"
            param-page="page"
        />
    </div>
    
    <!-- Delete confirmation modal -->
    <x-table.delete-modal />
</div>
