@extends('layouts.app')

@section('content')
    <x-layout.index-page entity="authors">
        @php
            $currentQuery = $listState->query();
            $formQuery = $listState->query(['q', 'page']);
            $sortQuery = $listState->query(['sort', 'direction', 'page']);
        @endphp

        <div class="space-y-6">
            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <x-list.search-form
                    :action="route('authors.index')"
                    :query="$formQuery"
                    :search="$listState->search"
                    placeholder="Search authors..."
                    :clear-url="route('authors.index')"
                />
            </div>

            <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                <table class="min-w-full divide-y divide-gray-200">
                    <x-table.header>
                        <x-list.sort-link label="Name" field="name" :current-sort="$listState->sort" :current-direction="$listState->direction" :url="route('authors.index')" :query="$sortQuery" />
                        <x-list.sort-link label="Internal Name" field="internal_name" :current-sort="$listState->sort" :current-direction="$listState->direction" :url="route('authors.index')" :query="$sortQuery" class="hidden md:table-cell" />
                        <x-list.sort-link label="Created" field="created_at" :current-sort="$listState->sort" :current-direction="$listState->direction" :url="route('authors.index')" :query="$sortQuery" class="hidden lg:table-cell" />
                        <x-table.header-cell hidden="hidden sm:table-cell">
                            <span class="sr-only">Actions</span>
                        </x-table.header-cell>
                    </x-table.header>

                    <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse($authors as $author)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                    <a href="{{ route('authors.show', $author) }}" class="text-yellow-700 hover:text-yellow-900 hover:underline">
                                        {{ $author->name }}
                                    </a>
                                </td>
                                <td class="hidden md:table-cell px-4 py-3 text-sm text-gray-500">{{ $author->internal_name ?? '—' }}</td>
                                <td class="hidden lg:table-cell px-4 py-3 text-xs text-gray-400">{{ optional($author->created_at)->format('Y-m-d H:i') }}</td>
                                <td class="hidden sm:table-cell px-4 py-3 text-right text-sm">
                                    <x-table.row-actions :view="route('authors.show', $author)" :edit="route('authors.edit', $author)" :delete="route('authors.destroy', $author)" delete-confirm="Delete this author?" entity="author" :record-id="$author->id" :record-name="$author->name" />
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-10 text-center text-sm text-gray-500">No authors found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <x-list.pagination :paginator="$authors" :action="route('authors.index')" :query="$currentQuery" :current-per-page="$listState->perPage" entity="authors" />
        </div>
    </x-layout.index-page>
@endsection
