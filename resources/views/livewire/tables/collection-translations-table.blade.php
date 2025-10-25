<div class="space-y-4">
    <x-table.filter-bar wireModel="q" placeholder="Search translations...">
        <div class="flex items-center gap-2">
            <label for="contextFilter" class="text-sm text-gray-700">Context:</label>
            <select wire:model.live="contextFilter" id="contextFilter" class="rounded-md border-gray-300 {{ $c['focus'] ?? '' }} text-sm">
                <option value="">All Contexts</option>
                <option value="default">Default Context Only</option>
                @foreach($contexts as $context)
                    <option value="{{ $context->id }}">{{ $context->internal_name }}</option>
                @endforeach
            </select>
        </div>

        <div class="flex items-center gap-2">
            <label for="languageFilter" class="text-sm text-gray-700">Language:</label>
            <select wire:model.live="languageFilter" id="languageFilter" class="rounded-md border-gray-300 {{ $c['focus'] ?? '' }} text-sm">
                <option value="">All Languages</option>
                <option value="default">Default Language Only</option>
                @foreach($languages as $language)
                    <option value="{{ $language->id }}">{{ $language->internal_name }}</option>
                @endforeach
            </select>
        </div>
    </x-table.filter-bar>

    <div class="bg-white shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <x-table.sortable-header field="title" label="Title" :sort-by="$sortBy" :sort-direction="$sortDirection" />
                    <th class="hidden md:table-cell px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Collection</th>
                    <th class="hidden lg:table-cell px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Language</th>
                    <th class="hidden lg:table-cell px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Context</th>
                    <th class="hidden xl:table-cell px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <button wire:click="sortBy('created_at')" 
                                class="group flex items-center space-x-1 hover:text-gray-700 transition-colors duration-200">
                            <span>Created</span>
                            <span class="flex flex-col">
                                @if($sortBy === 'created_at')
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
                    <th class="hidden sm:table-cell px-4 py-3">
                        <span class="sr-only">Actions</span>
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($collectionTranslations as $translation)
                    <tr class="hover:bg-gray-50 cursor-pointer" wire:key="translation-{{ $translation->id }}" onclick="window.location='{{ route('collection-translations.show', $translation) }}'">
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">
                            {{ $translation->title }}
                            @if($translation->description)
                                <div class="text-xs text-gray-500 mt-0.5 line-clamp-1">{{ Str::limit($translation->description, 60) }}</div>
                            @endif
                        </td>
                        <td class="hidden md:table-cell px-4 py-3 text-sm text-gray-700">
                            {{ $translation->collection?->internal_name ?? 'N/A' }}
                        </td>
                        <td class="hidden lg:table-cell px-4 py-3 text-sm text-gray-700">
                            {{ $translation->language?->internal_name ?? $translation->language_id }}
                        </td>
                        <td class="hidden lg:table-cell px-4 py-3 text-sm text-gray-700">
                            {{ $translation->context?->internal_name ?? 'N/A' }}
                            @if($translation->context?->is_default)
                                <span class="ml-1 inline-flex px-1.5 py-0.5 rounded text-xs bg-emerald-100 text-emerald-700">default</span>
                            @endif
                        </td>
                        <td class="hidden xl:table-cell px-4 py-3 text-xs text-gray-400">{{ optional($translation->created_at)->format('Y-m-d H:i') }}</td>
                        <td class="hidden sm:table-cell px-4 py-3 text-right text-sm" onclick="event.stopPropagation()">
                            <x-table.row-actions
                                :view="route('collection-translations.show', $translation)"
                                :edit="route('collection-translations.edit', $translation)"
                                :delete="route('collection-translations.destroy', $translation)"
                                delete-confirm="Delete this translation?"
                                entity="collection-translations"
                                :record-id="$translation->id"
                                :record-name="$translation->title"
                            />
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500">No translations found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>
        <x-layout.pagination 
            :paginator="$collectionTranslations" 
            entity="collection-translations"
            param-page="page"
        />
    </div>
    
    <!-- Delete confirmation modal -->
    <x-table.delete-modal />
</div>
