<div class="space-y-4">
    @php($c = $c ?? $entityColor('languages'))
    <x-table.filter-bar wireModel="q" placeholder="Search internal name..." />
    <div class="bg-white shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <x-table.header>
                <th class="hidden sm:table-cell px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    <button wire:click="sortBy('id')" 
                            class="group flex items-center space-x-1 hover:text-gray-700 transition-colors duration-200">
                        <span>Code</span>
                        <span class="flex flex-col">
                            @if($sortBy === 'id')
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
                <x-table.sortable-header field="internal_name" label="Internal Name" :sort-by="$sortBy" :sort-direction="$sortDirection" />
                <x-table.header-cell hidden="hidden lg:table-cell">Legacy</x-table.header-cell>
                <x-table.header-cell hidden="hidden md:table-cell">Default</x-table.header-cell>
                <x-table.header-cell hidden="hidden sm:table-cell">
                    <span class="sr-only">Actions</span>
                </x-table.header-cell>
            </x-table.header>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($languages as $language)
                    <tr class="hover:bg-gray-50 cursor-pointer" wire:key="language-{{ $language->id }}" onclick="window.location='{{ route('languages.show', $language) }}'">
                        <td class="hidden sm:table-cell px-4 py-3 text-sm font-mono">{{ $language->id }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900">{{ $language->internal_name }}</td>
                        <td class="hidden lg:table-cell px-4 py-3 text-sm text-gray-500">{{ $language->backward_compatibility ?? '—' }}</td>
                        <td class="hidden md:table-cell px-4 py-3 text-sm">{!! $language->is_default ? '<span class="text-green-600 font-medium">Yes</span>' : 'No' !!}</td>
                        <td class="hidden sm:table-cell px-4 py-3 text-right text-sm" onclick="event.stopPropagation()">
                            <x-table.row-actions
                                :view="route('languages.show', $language)"
                                :edit="route('languages.edit', $language)"
                                :delete="route('languages.destroy', $language)"
                                delete-confirm="Delete this language?"
                                entity="languages"
                                :record-id="$language->id"
                                :record-name="$language->internal_name"
                            />
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-sm text-gray-500">No languages found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div>
        <x-layout.pagination 
            :paginator="$languages" 
            entity="languages"
            param-page="page"
        />
    </div>
    
    <!-- Delete confirmation modal -->
    <x-table.delete-modal />
</div>
 
