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
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Internal Name</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Country</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Updated</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($partners as $partner)
                    <tr class="hover:bg-gray-50" wire:key="partner-{{ $partner->id }}">
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $partner->internal_name }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ $partner->type }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500">
                            @if($partner->country)
                                {{ $partner->country->internal_name }} ({{ $partner->country->id }})
                            @else
                                —
                            @endif
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-400">{{ optional($partner->created_at)->format('Y-m-d H:i') }}</td>
                        <td class="px-4 py-3 text-xs text-gray-400">{{ optional($partner->updated_at)->format('Y-m-d H:i') }}</td>
                        <td class="px-4 py-3 text-right text-sm">
                            <x-table.row-actions
                                :view="route('partners.show', $partner)"
                                :edit="route('partners.edit', $partner)"
                                :delete="route('partners.destroy', $partner)"
                                delete-confirm="Delete this partner?"
                                entity="partners"
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
            param-per-page="perPage" 
        />
    </div>
</div>
