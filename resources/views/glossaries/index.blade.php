@extends('layouts.app')

@section('content')
    <x-layout.index-page entity="glossary" createRoute="{{ route('glossaries.create') }}" createButtonText="Add Entry">
        @php
            $currentQuery = $listState->query();
            $formQuery = $listState->query(['q', 'page']);
            $sortQuery = $listState->query(['sort', 'direction', 'page']);
        @endphp

        <div class="space-y-6">
            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <x-list.search-form :action="route('glossaries.index')" :query="$formQuery" :search="$listState->search" placeholder="Search glossary entries..." :clear-url="route('glossaries.index')" />
            </div>

            <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                <table class="min-w-full divide-y divide-gray-200">
                    <x-table.header>
                        <x-list.sort-link label="Internal Name" field="internal_name" :current-sort="$listState->sort" :current-direction="$listState->direction" :url="route('glossaries.index')" :query="$sortQuery" />
                        <x-table.header-cell hidden="hidden md:table-cell">Translations</x-table.header-cell>
                        <x-table.header-cell hidden="hidden md:table-cell">Spellings</x-table.header-cell>
                        <x-list.sort-link label="Created" field="created_at" :current-sort="$listState->sort" :current-direction="$listState->direction" :url="route('glossaries.index')" :query="$sortQuery" class="hidden lg:table-cell" />
                        <x-table.header-cell hidden="hidden sm:table-cell"><span class="sr-only">Actions</span></x-table.header-cell>
                    </x-table.header>

                    <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse($glossaries as $glossary)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm font-medium text-gray-900"><a href="{{ route('glossaries.show', $glossary) }}" class="text-fuchsia-700 hover:text-fuchsia-900 hover:underline">{{ $glossary->internal_name }}</a></td>
                                <td class="hidden md:table-cell px-4 py-3 text-sm text-gray-500">
                                    <div class="flex flex-wrap gap-1">
                                        @forelse($glossary->translations as $translation)
                                            <x-ui.badge entity="glossaries" variant="pill">{{ $translation->language_id }}</x-ui.badge>
                                        @empty
                                            <span class="text-xs text-gray-400">—</span>
                                        @endforelse
                                    </div>
                                </td>
                                <td class="hidden md:table-cell px-4 py-3 text-sm text-gray-500">
                                    <div class="flex flex-wrap gap-1">
                                        @php($spellingsByLanguage = $glossary->spellings->groupBy('language_id'))
                                        @forelse($spellingsByLanguage as $languageId => $spellings)
                                            <x-ui.badge entity="glossaries" variant="pill">{{ $languageId }} ({{ $spellings->count() }})</x-ui.badge>
                                        @empty
                                            <span class="text-xs text-gray-400">—</span>
                                        @endforelse
                                    </div>
                                </td>
                                <td class="hidden lg:table-cell px-4 py-3 text-xs text-gray-400">{{ optional($glossary->created_at)->format('Y-m-d H:i') }}</td>
                                <td class="hidden sm:table-cell px-4 py-3 text-right text-sm"><x-table.row-actions :view="route('glossaries.show', $glossary)" :edit="route('glossaries.edit', $glossary)" :delete="route('glossaries.destroy', $glossary)" delete-confirm="Delete this glossary entry?" entity="glossary" :record-id="$glossary->id" :record-name="$glossary->internal_name" /></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-4 py-10 text-center text-sm text-gray-500">No glossary entries found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <x-list.pagination :paginator="$glossaries" :action="route('glossaries.index')" :query="$currentQuery" :current-per-page="$listState->perPage" entity="glossaries" />
        </div>
    </x-layout.index-page>
@endsection
