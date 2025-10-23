<div>
    <x-ui.table.search-and-pagination 
        entity="tags"
        wire:model.live.debounce.300ms="q"
        :per-page="$perPage"
        :paginator="$tags"
    />

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <x-ui.table.header entity="tags">
                <x-ui.table.th 
                    field="internal_name" 
                    :current-sort="$sortBy" 
                    :direction="$sortDirection"
                    wire:click="sortBy('internal_name')"
                >
                    Internal Name
                </x-ui.table.th>
                <x-ui.table.th 
                    field="description" 
                    :current-sort="$sortBy" 
                    :direction="$sortDirection"
                    wire:click="sortBy('description')"
                >
                    Description
                </x-ui.table.th>
                <x-ui.table.th 
                    field="backward_compatibility" 
                    :current-sort="$sortBy" 
                    :direction="$sortDirection"
                    wire:click="sortBy('backward_compatibility')"
                >
                    Legacy ID
                </x-ui.table.th>
                <x-ui.table.th 
                    field="created_at" 
                    :current-sort="$sortBy" 
                    :direction="$sortDirection"
                    wire:click="sortBy('created_at')"
                >
                    Created
                </x-ui.table.th>
                <x-ui.table.th 
                    field="updated_at" 
                    :current-sort="$sortBy" 
                    :direction="$sortDirection"
                    wire:click="sortBy('updated_at')"
                >
                    Updated
                </x-ui.table.th>
            </x-ui.table.header>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($tags as $tag)
                    <tr class="hover:bg-gray-50">
                        <x-ui.table.td>
                            <a href="{{ route('tags.show', $tag) }}" class="font-medium {{ $c['link'] ?? 'text-indigo-600 hover:text-indigo-900' }}">
                                {{ $tag->internal_name }}
                            </a>
                        </x-ui.table.td>
                        <x-ui.table.td>
                            {{ Str::limit($tag->description, 100) }}
                        </x-ui.table.td>
                        <x-ui.table.td>
                            {{ $tag->backward_compatibility }}
                        </x-ui.table.td>
                        <x-ui.table.td>
                            {{ $tag->created_at->format('Y-m-d H:i') }}
                        </x-ui.table.td>
                        <x-ui.table.td>
                            {{ $tag->updated_at->format('Y-m-d H:i') }}
                        </x-ui.table.td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                            No tags found
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <x-ui.table.pagination :paginator="$tags" />
</div>
