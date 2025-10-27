<div class="space-y-4">
    <x-table.filter-bar wireModel="q" placeholder="Search internal name..." />

    <div class="bg-white shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <x-table.header>
                <x-table.sortable-header field="internal_name" label="Internal Name" :sort-by="$sortBy" :sort-direction="$sortDirection" />
                <x-table.header-cell hidden="hidden md:table-cell">Type</x-table.header-cell>
                <x-table.header-cell hidden="hidden lg:table-cell">Country</x-table.header-cell>
                <x-table.sortable-header field="created_at" label="Created" :sort-by="$sortBy" :sort-direction="$sortDirection" class="hidden lg:table-cell" />
                <x-table.sortable-header field="updated_at" label="Updated" :sort-by="$sortBy" :sort-direction="$sortDirection" class="hidden lg:table-cell" />
                <x-table.header-cell hidden="hidden sm:table-cell">
                    <span class="sr-only">Actions</span>
                </x-table.header-cell>
            </x-table.header>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($partners as $partner)
                    <tr class="hover:bg-gray-50 cursor-pointer" wire:key="partner-{{ $partner->id }}" onclick="window.location='{{ route('partners.show', $partner) }}'">
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $partner->internal_name }}</td>
                        <td class="hidden md:table-cell px-4 py-3 text-sm text-gray-500">{{ $partner->type }}</td>
                        <td class="hidden lg:table-cell px-4 py-3 text-sm text-gray-500">
                            @if($partner->country)
                                {{ $partner->country->internal_name }} ({{ $partner->country->id }})
                            @else
                                —
                            @endif
                        </td>
                        <td class="hidden lg:table-cell px-4 py-3 text-xs text-gray-400">{{ optional($partner->created_at)->format('Y-m-d H:i') }}</td>
                        <td class="hidden lg:table-cell px-4 py-3 text-xs text-gray-400">{{ optional($partner->updated_at)->format('Y-m-d H:i') }}</td>
                        <td class="hidden sm:table-cell px-4 py-3 text-right text-sm" onclick="event.stopPropagation()">
                            <x-table.row-actions
                                :view="route('partners.show', $partner)"
                                :edit="route('partners.edit', $partner)"
                                :delete="route('partners.destroy', $partner)"
                                delete-confirm="Delete this partner?"
                                entity="partners"
                                :record-id="$partner->id"
                                :record-name="$partner->internal_name"
                            />
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500">No partners found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>
        <x-layout.pagination 
            :paginator="$partners" 
            entity="partners"
            param-page="page"
        />
    </div>
    
    <!-- Delete confirmation modal -->
    <x-table.delete-modal />
</div>
</div>
