@extends('layouts.app')

@section('content')
    <x-layout.index-page entity="partners">
        @php
            $currentQuery = $listState->query();
            $formQuery = $listState->query(['q', 'page']);
            $sortQuery = $listState->query(['sort', 'direction', 'page']);
        @endphp

        <div class="space-y-6">
            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <x-list.search-form
                    :action="route('partners.index')"
                    :query="$formQuery"
                    :search="$listState->search"
                    placeholder="Search internal names..."
                    :clear-url="route('partners.index')"
                />
            </div>

            <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                <table class="min-w-full divide-y divide-gray-200">
                    <x-table.header>
                        <x-list.sort-link
                            label="Internal Name"
                            field="internal_name"
                            :current-sort="$listState->sort"
                            :current-direction="$listState->direction"
                            :url="route('partners.index')"
                            :query="$sortQuery"
                        />
                        <x-table.header-cell hidden="hidden md:table-cell">Type</x-table.header-cell>
                        <x-table.header-cell hidden="hidden lg:table-cell">Country</x-table.header-cell>
                        <x-list.sort-link
                            label="Created"
                            field="created_at"
                            :current-sort="$listState->sort"
                            :current-direction="$listState->direction"
                            :url="route('partners.index')"
                            :query="$sortQuery"
                            class="hidden lg:table-cell"
                        />
                        <x-list.sort-link
                            label="Updated"
                            field="updated_at"
                            :current-sort="$listState->sort"
                            :current-direction="$listState->direction"
                            :url="route('partners.index')"
                            :query="$sortQuery"
                            class="hidden lg:table-cell"
                        />
                        <x-table.header-cell hidden="hidden sm:table-cell">
                            <span class="sr-only">Actions</span>
                        </x-table.header-cell>
                    </x-table.header>

                    <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse($partners as $partner)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                    <a href="{{ route('partners.show', $partner) }}" class="text-yellow-700 hover:text-yellow-900 hover:underline">
                                        {{ $partner->internal_name }}
                                    </a>
                                </td>
                                <td class="hidden md:table-cell px-4 py-3 text-sm text-gray-600">{{ $partner->type ?? '—' }}</td>
                                <td class="hidden lg:table-cell px-4 py-3 text-sm text-gray-500">
                                    <x-display.country-reference :country="$partner->country" />
                                </td>
                                <td class="hidden lg:table-cell px-4 py-3 text-xs text-gray-400">{{ optional($partner->created_at)->format('Y-m-d H:i') }}</td>
                                <td class="hidden lg:table-cell px-4 py-3 text-xs text-gray-400">{{ optional($partner->updated_at)->format('Y-m-d H:i') }}</td>
                                <td class="hidden sm:table-cell px-4 py-3 text-right text-sm">
                                    <x-table.row-actions
                                        :view="route('partners.show', $partner)"
                                        :edit="route('partners.edit', $partner)"
                                        :delete="route('partners.destroy', $partner)"
                                        delete-confirm="Delete this partner?"
                                        entity="partners"
                                        :record-id="$partner->id"
                                        :record-name="$partner->internal_name"
                                    />
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-10 text-center text-sm text-gray-500">
                                    No partners found for the current filters.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <x-list.pagination
                :paginator="$partners"
                :action="route('partners.index')"
                :query="$currentQuery"
                :current-per-page="$listState->perPage"
                entity="partners"
            />
        </div>
    </x-layout.index-page>
@endsection
