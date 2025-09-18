<div class="space-y-4">
    <div class="flex items-center gap-3">
        <div class="relative">
            <input wire:model.live.debounce.300ms="q" type="text" placeholder="Search internal name..." class="w-64 rounded-md border-gray-300 {{ $c['focus'] ?? '' }}" />
        </div>
        @if($q)
            <button wire:click="$set('q','')" type="button" class="text-sm text-gray-600 hover:underline">Clear</button>
        @endif
    </div>

    <div class="bg-white shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <x-table.sortable-header wire:click="sortBy('internal_name')" :active="$sortBy === 'internal_name'" :direction="$sortDirection">
                        Internal Name
                    </x-table.sortable-header>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Backward Compatibility</th>
                    <x-table.sortable-header wire:click="sortBy('created_at')" :active="$sortBy === 'created_at'" :direction="$sortDirection">
                        Created
                    </x-table.sortable-header>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Updated</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($items as $item)
                    <tr class="hover:bg-gray-50" wire:key="item-{{ $item->id }}">
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $item->internal_name }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ $item->backward_compatibility ?? '—' }}</td>
                        <td class="px-4 py-3 text-xs text-gray-400">{{ optional($item->created_at)->format('Y-m-d H:i') }}</td>
                        <td class="px-4 py-3 text-xs text-gray-400">{{ optional($item->updated_at)->format('Y-m-d H:i') }}</td>
                        <td class="px-4 py-3 text-right text-sm">
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
