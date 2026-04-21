@extends('layouts.app')

@section('content')
    <x-layout.index-page
        entity="item-item-links"
        title="Item Links"
        :create-route="route('item-links.create', $item)"
        create-button-text="Add Link"
    >
        @php
            $currentQuery = $listState->query(['item_id']);
            $formQuery = $listState->query(['q', 'page', 'item_id']);
            $sortQuery = $listState->query(['sort', 'direction', 'page', 'item_id']);
        @endphp

        <x-list.parent-context-header
            :breadcrumbs="[
                ['label' => 'Items', 'url' => route('items.index')],
                ['label' => $item->internal_name, 'url' => route('items.show', $item)],
                ['label' => 'Links'],
            ]"
            title="Links"
            parent-label="Item"
            :parent-value="$item->internal_name"
            :parent-url="route('items.show', $item)"
            :back-url="route('items.show', $item)"
            back-label="Back to Item"
        />

        <div class="space-y-6">
            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <x-list.search-form
                    :action="route('item-links.index', $item)"
                    :query="$formQuery"
                    :search="$listState->search"
                    placeholder="Search links..."
                    :clear-url="route('item-links.index', $item)"
                />
            </div>

            <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                <table class="min-w-full divide-y divide-gray-200">
                    <x-table.header>
                        <x-list.sort-link
                            label="Target Item"
                            field="target.internal_name"
                            :current-sort="$listState->sort"
                            :current-direction="$listState->direction"
                            :url="route('item-links.index', $item)"
                            :query="$sortQuery"
                        />
                        <x-list.sort-link
                            label="Context"
                            field="created_at"
                            :current-sort="$listState->sort"
                            :current-direction="$listState->direction"
                            :url="route('item-links.index', $item)"
                            :query="$sortQuery"
                            class="hidden md:table-cell"
                        />
                        <x-list.sort-link
                            label="Created"
                            field="created_at"
                            :current-sort="$listState->sort"
                            :current-direction="$listState->direction"
                            :url="route('item-links.index', $item)"
                            :query="$sortQuery"
                            class="hidden lg:table-cell"
                        />
                        <x-table.header-cell hidden="hidden sm:table-cell">
                            <span class="sr-only">Actions</span>
                        </x-table.header-cell>
                    </x-table.header>

                    <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse($itemItemLinks as $link)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                    <a href="{{ route('item-links.show', ['item' => $item, 'itemItemLink' => $link]) }}" class="text-teal-700 hover:text-teal-900 hover:underline">
                                        {{ $link->target?->internal_name ?? '—' }}
                                    </a>
                                </td>
                                <td class="hidden md:table-cell px-4 py-3 text-sm text-gray-600">{{ $link->context?->internal_name ?? '—' }}</td>
                                <td class="hidden lg:table-cell px-4 py-3 text-xs text-gray-400">{{ optional($link->created_at)->format('Y-m-d H:i') }}</td>
                                <td class="hidden sm:table-cell px-4 py-3 text-right text-sm">
                                    <x-table.row-actions
                                        :view="route('item-links.show', ['item' => $item, 'itemItemLink' => $link])"
                                        :edit="route('item-links.edit', ['item' => $item, 'itemItemLink' => $link])"
                                        :delete="route('item-links.destroy', ['item' => $item, 'itemItemLink' => $link])"
                                        delete-confirm="Delete this item link?"
                                        entity="item-item-links"
                                        :record-id="$link->id"
                                        :record-name="$link->target?->internal_name"
                                    />
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-10 text-center text-sm text-gray-500">
                                    No links found for the current filters.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <x-list.pagination
                :paginator="$itemItemLinks"
                :action="route('item-links.index', $item)"
                :query="$currentQuery"
                :current-per-page="$listState->perPage"
                entity="item-item-links"
            />
        </div>
    </x-layout.index-page>
@endsection

