@extends('layouts.app')

@section('content')
    <x-layout.index-page entity="languages">
        @php
            $currentQuery = $listState->query();
            $formQuery = $listState->query(['q', 'page']);
            $sortQuery = $listState->query(['sort', 'direction', 'page']);
        @endphp

        <div class="space-y-6">
            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <x-list.search-form :action="route('languages.index')" :query="$formQuery" :search="$listState->search" placeholder="Search code or internal name..." :clear-url="route('languages.index')" />
            </div>

            <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                <table class="min-w-full divide-y divide-gray-200">
                    <x-table.header>
                        <x-list.sort-link label="Code" field="id" :current-sort="$listState->sort" :current-direction="$listState->direction" :url="route('languages.index')" :query="$sortQuery" class="hidden sm:table-cell" />
                        <x-list.sort-link label="Internal Name" field="internal_name" :current-sort="$listState->sort" :current-direction="$listState->direction" :url="route('languages.index')" :query="$sortQuery" />
                        <x-table.header-cell hidden="hidden lg:table-cell">Legacy</x-table.header-cell>
                        <x-list.sort-link label="Default" field="is_default" :current-sort="$listState->sort" :current-direction="$listState->direction" :url="route('languages.index')" :query="$sortQuery" class="hidden md:table-cell" />
                        <x-table.header-cell hidden="hidden sm:table-cell"><span class="sr-only">Actions</span></x-table.header-cell>
                    </x-table.header>

                    <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse($languages as $language)
                            <tr class="hover:bg-gray-50">
                                <td class="hidden sm:table-cell px-4 py-3 text-sm font-mono">{{ $language->id }}</td>
                                <td class="px-4 py-3 text-sm text-gray-900"><a href="{{ route('languages.show', $language) }}" class="text-fuchsia-700 hover:text-fuchsia-900 hover:underline">{{ $language->internal_name }}</a></td>
                                <td class="hidden lg:table-cell px-4 py-3 text-sm text-gray-500">{{ $language->backward_compatibility ?? '—' }}</td>
                                <td class="hidden md:table-cell px-4 py-3 text-sm"><x-ui.badge :color="$language->is_default ? 'green' : 'gray'" variant="pill">{{ $language->is_default ? 'Yes' : 'No' }}</x-ui.badge></td>
                                <td class="hidden sm:table-cell px-4 py-3 text-right text-sm"><x-table.row-actions :view="route('languages.show', $language)" :edit="route('languages.edit', $language)" :delete="route('languages.destroy', $language)" delete-confirm="Delete this language?" entity="languages" :record-id="$language->id" :record-name="$language->internal_name" /></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-4 py-10 text-center text-sm text-gray-500">No languages found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <x-list.pagination :paginator="$languages" :action="route('languages.index')" :query="$currentQuery" :current-per-page="$listState->perPage" entity="languages" />
        </div>
    </x-layout.index-page>
@endsection
