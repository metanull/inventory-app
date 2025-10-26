<div class="space-y-4">
    <x-table.filter-bar wireModel="q" placeholder="Search internal name..." />

    <div class="bg-white shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <x-table.sortable-header field="internal_name" label="Internal Name" :sort-by="$sortBy" :sort-direction="$sortDirection" />
                    <x-table.sortable-header field="is_default" label="Default" :sort-by="$sortBy" :sort-direction="$sortDirection" class="hidden md:table-cell" />
                    <x-table.sortable-header field="created_at" label="Created" :sort-by="$sortBy" :sort-direction="$sortDirection" class="hidden lg:table-cell" />
                    </th>
                    <th class="hidden sm:table-cell px-4 py-3">
                        <span class="sr-only">Actions</span>
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($contexts as $context)
                    <tr class="hover:bg-gray-50 cursor-pointer" wire:key="context-{{ $context->id }}" onclick="window.location='{{ route('contexts.show', $context) }}'">
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $context->internal_name }}</td>
                        <td class="hidden md:table-cell px-4 py-3 text-sm">
                            <span class="inline-flex px-2 py-0.5 rounded text-xs {{ $context->is_default ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-600' }}">{{ $context->is_default ? 'Yes' : 'No' }}</span>
                        </td>
                        <td class="hidden lg:table-cell px-4 py-3 text-xs text-gray-400">{{ optional($context->created_at)->format('Y-m-d H:i') }}</td>
                        <td class="hidden sm:table-cell px-4 py-3 text-right text-sm" onclick="event.stopPropagation()">
                            <x-table.row-actions
                                :view="route('contexts.show', $context)"
                                :edit="route('contexts.edit', $context)"
                                :delete="route('contexts.destroy', $context)"
                                delete-confirm="Delete this context?"
                                entity="contexts"
                                :record-id="$context->id"
                                :record-name="$context->internal_name"
                            />
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-8 text-center text-sm text-gray-500">No contexts found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>
        <x-layout.pagination 
            :paginator="$contexts" 
            entity="contexts"
            param-page="page"
        />
    </div>
    
    <!-- Delete confirmation modal -->
    <x-table.delete-modal />
</div>
</div>
