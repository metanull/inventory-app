<div class="space-y-4">
    @php($c = $c ?? $entityColor('countries'))
    <x-table.filter-bar wireModel="q" placeholder="Search internal name..." />

    <div class="bg-white shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
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
                    <th class="hidden md:table-cell px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Legacy</th>
                    <th class="hidden sm:table-cell px-4 py-3">
                        <span class="sr-only">Actions</span>
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($countries as $country)
                    <tr class="hover:bg-gray-50 cursor-pointer" wire:key="country-{{ $country->id }}" onclick="window.location='{{ route('countries.show', $country) }}'">
                        <td class="hidden sm:table-cell px-4 py-3 text-sm font-mono">{{ $country->id }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900">{{ $country->internal_name }}</td>
                        <td class="hidden md:table-cell px-4 py-3 text-sm text-gray-500">{{ $country->backward_compatibility ?? '—' }}</td>
                        <td class="hidden sm:table-cell px-4 py-3 text-right text-sm" onclick="event.stopPropagation()">
                            <x-table.row-actions
                                :view="route('countries.show', $country)"
                                :edit="route('countries.edit', $country)"
                                :delete="route('countries.destroy', $country)"
                                delete-confirm="Delete this country?"
                                entity="countries"
                                :record-id="$country->id"
                                :record-name="$country->internal_name"
                            />
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-8 text-center text-sm text-gray-500">No countries found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div>
        <x-layout.pagination 
            :paginator="$countries" 
            entity="countries"
            param-page="page"
        />
    </div>
    
    <!-- Delete confirmation modal -->
    <x-table.delete-modal />
</div>
 
