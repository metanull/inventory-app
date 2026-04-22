@extends('layouts.app')

@section('content')
    <x-layout.index-page
        entity="glossary-translations"
        title="Translations"
        :create-route="route('glossaries.translations.create', $glossary)"
        create-button-text="Add Translation"
    >
        @php
            $currentQuery = $listState->query(['glossary_id']);
            $formQuery = $listState->query(['q', 'page', 'glossary_id']);
            $sortQuery = $listState->query(['sort', 'direction', 'page', 'glossary_id']);
        @endphp

        <x-list.parent-context-header
            :breadcrumbs="[
                ['label' => 'Glossaries', 'url' => route('glossaries.index')],
                ['label' => $glossary->internal_name, 'url' => route('glossaries.show', $glossary)],
                ['label' => 'Translations'],
            ]"
            title="Translations"
            parent-label="Glossary"
            :parent-value="$glossary->internal_name"
            :parent-url="route('glossaries.show', $glossary)"
            :back-url="route('glossaries.show', $glossary)"
            back-label="Back to Glossary"
        />

        <div class="space-y-6">
            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <x-list.search-form
                    :action="route('glossaries.translations.index', $glossary)"
                    :query="$formQuery"
                    :search="$listState->search"
                    placeholder="Search translations..."
                    :clear-url="route('glossaries.translations.index', $glossary)"
                >
                    <div class="w-full md:w-52">
                        <label for="language" class="mb-1 block text-xs font-medium uppercase tracking-wide text-gray-500">Language</label>
                        <select id="language" name="language" class="block w-full rounded-md border-gray-300 text-sm focus:border-teal-500 focus:ring-teal-500">
                            <option value="">All languages</option>
                            @foreach($languages as $lang)
                                <option value="{{ $lang->id }}" @selected(($listState->filters['language'] ?? null) === $lang->id)>{{ $lang->internal_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </x-list.search-form>
            </div>

            <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                <table class="min-w-full divide-y divide-gray-200">
                    <x-table.header>
                        <x-list.sort-link
                            label="Language"
                            field="language.internal_name"
                            :current-sort="$listState->sort"
                            :current-direction="$listState->direction"
                            :url="route('glossaries.translations.index', $glossary)"
                            :query="$sortQuery"
                        />
                        <x-table.header-cell hidden="hidden md:table-cell">Definition</x-table.header-cell>
                        <x-list.sort-link
                            label="Updated"
                            field="updated_at"
                            :current-sort="$listState->sort"
                            :current-direction="$listState->direction"
                            :url="route('glossaries.translations.index', $glossary)"
                            :query="$sortQuery"
                            class="hidden lg:table-cell"
                        />
                        <x-table.header-cell hidden="hidden sm:table-cell">
                            <span class="sr-only">Actions</span>
                        </x-table.header-cell>
                    </x-table.header>

                    <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse($translations as $translation)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                    <a href="{{ route('glossaries.translations.show', [$glossary, $translation]) }}" class="text-teal-700 hover:text-teal-900 hover:underline">
                                        {{ $translation->language?->internal_name ?? '—' }}
                                    </a>
                                </td>
                                <td class="hidden md:table-cell px-4 py-3 text-sm text-gray-600">
                                    {{ \Illuminate\Support\Str::limit($translation->definition ?? '', 80) }}
                                </td>
                                <td class="hidden lg:table-cell px-4 py-3 text-xs text-gray-400">{{ optional($translation->updated_at)->format('Y-m-d H:i') }}</td>
                                <td class="hidden sm:table-cell px-4 py-3 text-right text-sm">
                                    <x-table.row-actions
                                        :view="route('glossaries.translations.show', [$glossary, $translation])"
                                        :edit="route('glossaries.translations.edit', [$glossary, $translation])"
                                        :delete="route('glossaries.translations.destroy', [$glossary, $translation])"
                                        delete-confirm="Delete this translation?"
                                        entity="glossary-translations"
                                        :record-id="$translation->id"
                                        :record-name="$translation->language?->internal_name"
                                    />
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-10 text-center text-sm text-gray-500">
                                    No translations found for the current filters.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <x-list.pagination
                :paginator="$translations"
                :action="route('glossaries.translations.index', $glossary)"
                :query="$currentQuery"
                :current-per-page="$listState->perPage"
                entity="glossary-translations"
            />
        </div>
    </x-layout.index-page>
@endsection
