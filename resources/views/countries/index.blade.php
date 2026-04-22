@extends('layouts.app')

@section('content')
    <x-layout.index-page entity="countries">
        @php
            $currentQuery = $listState->query();
            $formQuery = $listState->query(['q', 'page']);
            $sortQuery = $listState->query(['sort', 'direction', 'page']);
        @endphp

        <div class="space-y-6">
            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <x-list.search-form :action="route('countries.index')" :query="$formQuery" :search="$listState->search" placeholder="Search code or internal name..." :clear-url="route('countries.index')" />
            </div>

            <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                <table class="min-w-full divide-y divide-gray-200">
                    <x-table.header>
                        <x-list.sort-link label="Code" field="id" :current-sort="$listState->sort" :current-direction="$listState->direction" :url="route('countries.index')" :query="$sortQuery" class="hidden sm:table-cell" />
                        <x-list.sort-link label="Internal Name" field="internal_name" :current-sort="$listState->sort" :current-direction="$listState->direction" :url="route('countries.index')" :query="$sortQuery" />
                        <x-table.header-cell hidden="hidden md:table-cell">Legacy</x-table.header-cell>
                        <x-table.header-cell hidden="hidden sm:table-cell"><span class="sr-only">Actions</span></x-table.header-cell>
                    </x-table.header>

                    <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse($countries as $country)
                            <tr class="hover:bg-gray-50">
                                <td class="hidden sm:table-cell px-4 py-3 text-sm font-mono">{{ $country->id }}</td>
                                <td class="px-4 py-3 text-sm text-gray-900"><a href="{{ route('countries.show', $country) }}" class="text-indigo-700 hover:text-indigo-900 hover:underline">{{ $country->internal_name }}</a></td>
                                <td class="hidden md:table-cell px-4 py-3 text-sm text-gray-500">{{ $country->backward_compatibility ?? '—' }}</td>
                                <td class="hidden sm:table-cell px-4 py-3 text-right text-sm"><x-table.row-actions :view="route('countries.show', $country)" :edit="route('countries.edit', $country)" :delete="route('countries.destroy', $country)" delete-confirm="Delete this country?" entity="countries" :record-id="$country->id" :record-name="$country->internal_name" /></td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-4 py-10 text-center text-sm text-gray-500">No countries found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <x-list.pagination :paginator="$countries" :action="route('countries.index')" :query="$currentQuery" :current-per-page="$listState->perPage" entity="countries" />
        </div>
    </x-layout.index-page>
@endsection
