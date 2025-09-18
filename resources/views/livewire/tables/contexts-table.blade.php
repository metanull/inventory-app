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
                    <x-table.sortable-header field="internal_name" label="Internal Name" :sort-by="$sortBy" :sort-direction="$sortDirection" />
                    <x-table.sortable-header field="is_default" label="Default" :sort-by="$sortBy" :sort-direction="$sortDirection" />
                    <x-table.sortable-header field="created_at" label="Created" :sort-by="$sortBy" :sort-direction="$sortDirection" />
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($contexts as $context)
                    <tr class="hover:bg-gray-50" wire:key="context-{{ $context->id }}">
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $context->internal_name }}</td>
                        <td class="px-4 py-3 text-sm">
                            <span class="inline-flex px-2 py-0.5 rounded text-xs {{ $context->is_default ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-600' }}">{{ $context->is_default ? 'Yes' : 'No' }}</span>
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-400">{{ optional($context->created_at)->format('Y-m-d H:i') }}</td>
                        <td class="px-4 py-3 text-right text-sm">
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
