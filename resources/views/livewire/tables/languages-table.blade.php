<div class="space-y-4">
    @php($c = $c ?? $entityColor('languages'))
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
                    <x-table.sortable-header field="id" label="Code" :sort-by="$sortBy" :sort-direction="$sortDirection" />
                    <x-table.sortable-header field="internal_name" label="Internal Name" :sort-by="$sortBy" :sort-direction="$sortDirection" />
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Legacy</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Default</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($languages as $language)
                    <tr class="hover:bg-gray-50" wire:key="language-{{ $language->id }}">
                        <td class="px-4 py-3 text-sm font-mono">{{ $language->id }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900">{{ $language->internal_name }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ $language->backward_compatibility ?? '—' }}</td>
                        <td class="px-4 py-3 text-sm">{!! $language->is_default ? '<span class="text-green-600 font-medium">Yes</span>' : 'No' !!}</td>
                        <td class="px-4 py-3 text-right text-sm">
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
 
