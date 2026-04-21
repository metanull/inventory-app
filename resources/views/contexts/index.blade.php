@extends('layouts.app')

@section('content')
    <x-layout.index-page entity="contexts">
        @php
            $currentQuery = $listState->query();
            $formQuery = $listState->query(['q', 'page']);
            $sortQuery = $listState->query(['sort', 'direction', 'page']);
        @endphp

        <div class="space-y-6">
            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <x-list.search-form :action="route('contexts.index')" :query="$formQuery" :search="$listState->search" placeholder="Search internal names..." :clear-url="route('contexts.index')" />
            </div>

            <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                <table class="min-w-full divide-y divide-gray-200">
                    <x-table.header>
                        <x-list.sort-link label="Internal Name" field="internal_name" :current-sort="$listState->sort" :current-direction="$listState->direction" :url="route('contexts.index')" :query="$sortQuery" />
                        <x-list.sort-link label="Default" field="is_default" :current-sort="$listState->sort" :current-direction="$listState->direction" :url="route('contexts.index')" :query="$sortQuery" class="hidden md:table-cell" />
                        <x-list.sort-link label="Created" field="created_at" :current-sort="$listState->sort" :current-direction="$listState->direction" :url="route('contexts.index')" :query="$sortQuery" class="hidden lg:table-cell" />
                        <x-table.header-cell hidden="hidden sm:table-cell"><span class="sr-only">Actions</span></x-table.header-cell>
                    </x-table.header>

                    <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse($contexts as $context)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm font-medium text-gray-900"><a href="{{ route('contexts.show', $context) }}" class="text-indigo-700 hover:text-indigo-900 hover:underline">{{ $context->internal_name }}</a></td>
                                <td class="hidden md:table-cell px-4 py-3 text-sm"><x-ui.badge :color="$context->is_default ? 'green' : 'gray'" variant="pill">{{ $context->is_default ? 'Yes' : 'No' }}</x-ui.badge></td>
                                <td class="hidden lg:table-cell px-4 py-3 text-xs text-gray-400">{{ optional($context->created_at)->format('Y-m-d H:i') }}</td>
                                <td class="hidden sm:table-cell px-4 py-3 text-right text-sm"><x-table.row-actions :view="route('contexts.show', $context)" :edit="route('contexts.edit', $context)" :delete="route('contexts.destroy', $context)" delete-confirm="Delete this context?" entity="contexts" :record-id="$context->id" :record-name="$context->internal_name" /></td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-4 py-10 text-center text-sm text-gray-500">No contexts found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <x-list.pagination :paginator="$contexts" :action="route('contexts.index')" :query="$currentQuery" :current-per-page="$listState->perPage" entity="contexts" />
        </div>
    </x-layout.index-page>
@endsection
