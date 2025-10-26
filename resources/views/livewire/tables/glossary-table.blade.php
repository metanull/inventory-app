<div class="space-y-4">
    <x-table.filter-bar wireModel="q" placeholder="Search internal name..." />

    <div class="bg-white shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <x-table.sortable-header field="internal_name" label="Internal Name" :sort-by="$sortBy" :sort-direction="$sortDirection" />
                    <th class="hidden md:table-cell px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Translations
                    </th>
                    <th class="hidden md:table-cell px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Spellings
                    </th>
                    <x-table.sortable-header field="created_at" label="Created" :sort-by="$sortBy" :sort-direction="$sortDirection" class="hidden lg:table-cell" />
                    <th class="hidden sm:table-cell px-4 py-3">
                        <span class="sr-only">Actions</span>
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($glossaries as $glossary)
                    <tr class="hover:bg-gray-50 cursor-pointer" wire:key="glossary-{{ $glossary->id }}" onclick="window.location='{{ route('glossaries.show', $glossary) }}'">
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $glossary->internal_name }}</td>
                        <td class="hidden md:table-cell px-4 py-3 text-sm text-gray-500">
                            <div class="flex flex-wrap gap-1">
                                @forelse($glossary->translations as $translation)
                                    <x-ui.badge color="blue" variant="pill">
                                        {{ $translation->language_id }}
                                    </x-ui.badge>
                                @empty
                                    <span class="text-gray-400 text-xs">—</span>
                                @endforelse
                            </div>
                        </td>
                        <td class="hidden md:table-cell px-4 py-3 text-sm text-gray-500">
                            <div class="flex flex-wrap gap-1">
                                @php
                                    $spellingsByLanguage = $glossary->spellings->groupBy('language_id');
                                @endphp
                                @forelse($spellingsByLanguage as $languageId => $spellings)
                                    <x-ui.badge color="green" variant="pill">
                                        {{ $languageId }} ({{ $spellings->count() }})
                                    </x-ui.badge>
                                @empty
                                    <span class="text-gray-400 text-xs">—</span>
                                @endforelse
                            </div>
                        </td>
                        <td class="hidden lg:table-cell px-4 py-3 text-xs text-gray-400">{{ optional($glossary->created_at)->format('Y-m-d H:i') }}</td>
                        <td class="hidden sm:table-cell px-4 py-3 text-right text-sm" onclick="event.stopPropagation()">
                            <x-table.row-actions
                                :view="route('glossaries.show', $glossary)"
                                :edit="route('glossaries.edit', $glossary)"
                                :delete="route('glossaries.destroy', $glossary)"
                                delete-confirm="Delete this glossary entry?"
                                entity="glossary"
                                :record-id="$glossary->id"
                                :record-name="$glossary->internal_name"
                            />
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-sm text-gray-500">No glossary entries found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>
        <x-layout.pagination 
            :paginator="$glossaries" 
            entity="glossary"
            param-page="page"
        />
    </div>
    
    <!-- Delete confirmation modal -->
    <x-table.delete-modal />
</div>
