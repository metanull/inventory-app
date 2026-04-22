@extends('layouts.app')

@section('content')
    <x-layout.index-page
        entity="glossary-spellings"
        title="Spellings"
        :create-route="route('glossaries.spellings.create', $glossary)"
        create-button-text="Add Spelling"
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
                ['label' => 'Spellings'],
            ]"
            title="Spellings"
            parent-label="Glossary"
            :parent-value="$glossary->internal_name"
            :parent-url="route('glossaries.show', $glossary)"
            :back-url="route('glossaries.show', $glossary)"
            back-label="Back to Glossary"
        />

        <div class="space-y-6">
            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <x-list.search-form
                    :action="route('glossaries.spellings.index', $glossary)"
                    :query="$formQuery"
                    :search="$listState->search"
                    placeholder="Search spellings..."
                    :clear-url="route('glossaries.spellings.index', $glossary)"
                />
            </div>

            <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                <table class="min-w-full divide-y divide-gray-200">
                    <x-table.header>
                        <x-list.sort-link
                            label="Spelling"
                            field="spelling"
                            :current-sort="$listState->sort"
                            :current-direction="$listState->direction"
                            :url="route('glossaries.spellings.index', $glossary)"
                            :query="$sortQuery"
                        />
                        <x-table.header-cell hidden="hidden md:table-cell">Language</x-table.header-cell>
                        <x-list.sort-link
                            label="Created"
                            field="created_at"
                            :current-sort="$listState->sort"
                            :current-direction="$listState->direction"
                            :url="route('glossaries.spellings.index', $glossary)"
                            :query="$sortQuery"
                            class="hidden lg:table-cell"
                        />
                        <x-table.header-cell hidden="hidden sm:table-cell">
                            <span class="sr-only">Actions</span>
                        </x-table.header-cell>
                    </x-table.header>

                    <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse($spellings as $spelling)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                    <a href="{{ route('glossaries.spellings.show', [$glossary, $spelling]) }}" class="text-teal-700 hover:text-teal-900 hover:underline">
                                        {{ $spelling->spelling }}
                                    </a>
                                </td>
                                <td class="hidden md:table-cell px-4 py-3 text-sm text-gray-600">{{ $spelling->language?->internal_name ?? '—' }}</td>
                                <td class="hidden lg:table-cell px-4 py-3 text-xs text-gray-400">{{ optional($spelling->created_at)->format('Y-m-d H:i') }}</td>
                                <td class="hidden sm:table-cell px-4 py-3 text-right text-sm">
                                    <x-table.row-actions
                                        :view="route('glossaries.spellings.show', [$glossary, $spelling])"
                                        :edit="route('glossaries.spellings.edit', [$glossary, $spelling])"
                                        :delete="route('glossaries.spellings.destroy', [$glossary, $spelling])"
                                        delete-confirm="Delete this spelling?"
                                        entity="glossary-spellings"
                                        :record-id="$spelling->id"
                                        :record-name="$spelling->spelling"
                                    />
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-10 text-center text-sm text-gray-500">
                                    No spellings found for the current filters.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <x-list.pagination
                :paginator="$spellings"
                :action="route('glossaries.spellings.index', $glossary)"
                :query="$currentQuery"
                :current-per-page="$listState->perPage"
                entity="glossary-spellings"
            />
        </div>
    </x-layout.index-page>
@endsection
