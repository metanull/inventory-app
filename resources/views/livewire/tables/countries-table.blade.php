<div class="space-y-4">
    @php($c = $c ?? $entityColor('countries'))
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
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Code</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Internal Name</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Legacy</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($countries as $country)
                    <tr class="hover:bg-gray-50" wire:key="country-{{ $country->id }}">
                        <td class="px-4 py-3 text-sm font-mono">{{ $country->id }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900">{{ $country->internal_name }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ $country->backward_compatibility ?? '—' }}</td>
                        <td class="px-4 py-3 text-right text-sm">
                            <x-table.row-actions
                                :view="route('countries.show', $country)"
                                :edit="route('countries.edit', $country)"
                                :delete="route('countries.destroy', $country)"
                                delete-confirm="Delete this country?"
                                entity="countries"
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
</div>
 
