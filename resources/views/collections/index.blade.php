@extends('layouts.app')

@section('content')
    <x-layout.index-page entity="collections">
        @php
            $cleanQuery = static fn (array $query): array => array_filter(
                $query,
                static fn (mixed $value): bool => $value !== null && $value !== [] && $value !== ''
            );
            $currentQuery = $listState->query();
            $formQuery = $listState->query(['q', 'page']);
            $sortQuery = $listState->query(['sort', 'direction', 'page']);
            $hierarchyQuery = $listState->query(['page', 'parent_id', 'mode']);
            $flatQuery = $listState->query(['page', 'parent_id', 'mode']);
        @endphp

        <div class="space-y-6">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="inline-flex overflow-hidden rounded-md border border-gray-200 bg-white shadow-sm">
                    <a
                        href="{{ route('collections.index', array_merge($hierarchyQuery, ['mode' => 'hierarchy'])) }}"
                        class="px-4 py-2 text-sm font-medium {{ $hierarchyMode ? 'bg-yellow-600 text-white' : 'text-gray-700 hover:bg-gray-50' }}"
                    >
                        Hierarchy
                    </a>
                    <a
                        href="{{ route('collections.index', array_merge($flatQuery, ['mode' => 'flat'])) }}"
                        class="px-4 py-2 text-sm font-medium {{ ! $hierarchyMode ? 'bg-yellow-600 text-white' : 'text-gray-700 hover:bg-gray-50' }}"
                    >
                        Flat
                    </a>
                </div>

                @if($hierarchyMode && $parentCollection)
                    <x-ui.button
                        :href="route('collections.index', $cleanQuery(array_merge($hierarchyQuery, ['mode' => 'hierarchy', 'parent_id' => $parentCollection->parent_id])))"
                        variant="secondary"
                        size="sm"
                    >
                        Back
                    </x-ui.button>
                @endif
            </div>

            @if($hierarchyMode)
                <nav class="flex flex-wrap items-center gap-1 text-sm text-gray-500" aria-label="Breadcrumb">
                    <a href="{{ route('collections.index', array_merge($hierarchyQuery, ['mode' => 'hierarchy'])) }}" class="hover:text-yellow-700">All Collections</a>
                    @foreach($breadcrumbs as $crumb)
                        <x-heroicon-o-chevron-right class="h-4 w-4 shrink-0" />
                        @if($loop->last)
                            <span class="font-medium text-gray-900">{{ $crumb['label'] }}</span>
                        @else
                            <a
                                href="{{ route('collections.index', $cleanQuery(array_merge($hierarchyQuery, ['mode' => 'hierarchy', 'parent_id' => $crumb['id']]))) }}"
                                class="hover:text-yellow-700"
                            >
                                {{ $crumb['label'] }}
                            </a>
                        @endif
                    @endforeach
                </nav>
            @endif

            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <x-list.search-form
                    :action="route('collections.index')"
                    :query="$formQuery"
                    :search="$listState->search"
                    placeholder="Search internal names..."
                    :clear-url="route('collections.index')"
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
                            :url="route('collections.index')"
                            :query="$sortQuery"
                        />
                        <x-table.header-cell hidden="hidden md:table-cell">Language</x-table.header-cell>
                        <x-table.header-cell hidden="hidden lg:table-cell">Context</x-table.header-cell>
                        @if($hierarchyMode)
                            <x-table.header-cell hidden="hidden md:table-cell">Children</x-table.header-cell>
                        @endif
                        <x-list.sort-link
                            label="Order"
                            field="display_order"
                            :current-sort="$listState->sort"
                            :current-direction="$listState->direction"
                            :url="route('collections.index')"
                            :query="$sortQuery"
                            class="hidden lg:table-cell"
                        />
                        <x-list.sort-link
                            label="Created"
                            field="created_at"
                            :current-sort="$listState->sort"
                            :current-direction="$listState->direction"
                            :url="route('collections.index')"
                            :query="$sortQuery"
                            class="hidden xl:table-cell"
                        />
                        <x-list.sort-link
                            label="Updated"
                            field="updated_at"
                            :current-sort="$listState->sort"
                            :current-direction="$listState->direction"
                            :url="route('collections.index')"
                            :query="$sortQuery"
                            class="hidden 2xl:table-cell"
                        />
                        <x-table.header-cell hidden="hidden sm:table-cell">
                            <span class="sr-only">Actions</span>
                        </x-table.header-cell>
                    </x-table.header>

                    <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse($collections as $collection)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                    <div class="space-y-1">
                                        <a href="{{ route('collections.show', $collection) }}" class="text-yellow-700 hover:text-yellow-900 hover:underline">
                                            {{ $collection->internal_name }}
                                        </a>

                                        @if($hierarchyMode && $collection->children_count > 0)
                                            <div>
                                                <a
                                                    href="{{ route('collections.index', $cleanQuery(array_merge($hierarchyQuery, ['mode' => 'hierarchy', 'parent_id' => $collection->id]))) }}"
                                                    class="inline-flex items-center gap-1 text-xs text-yellow-600 hover:text-yellow-800"
                                                >
                                                    <span>Browse children</span>
                                                    <x-heroicon-o-chevron-right class="h-3.5 w-3.5" />
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td class="hidden md:table-cell px-4 py-3 text-sm text-gray-500">{{ $collection->language->internal_name ?? $collection->language_id }}</td>
                                <td class="hidden lg:table-cell px-4 py-3 text-sm text-gray-500">{{ $collection->context->internal_name ?? '—' }}</td>
                                @if($hierarchyMode)
                                    <td class="hidden md:table-cell px-4 py-3 text-sm text-gray-500">
                                        @if($collection->children_count > 0)
                                            <span class="inline-flex items-center rounded-full bg-yellow-100 px-2 py-0.5 text-xs font-medium text-yellow-800">
                                                {{ $collection->children_count }}
                                            </span>
                                        @else
                                            <span class="text-gray-400">—</span>
                                        @endif
                                    </td>
                                @endif
                                <td class="hidden lg:table-cell px-4 py-3 text-sm text-gray-500">{{ $collection->display_order ?? '—' }}</td>
                                <td class="hidden xl:table-cell px-4 py-3 text-xs text-gray-400">{{ optional($collection->created_at)->format('Y-m-d H:i') }}</td>
                                <td class="hidden 2xl:table-cell px-4 py-3 text-xs text-gray-400">{{ optional($collection->updated_at)->format('Y-m-d H:i') }}</td>
                                <td class="hidden sm:table-cell px-4 py-3 text-right text-sm">
                                    <x-table.row-actions
                                        :view="route('collections.show', $collection)"
                                        :edit="route('collections.edit', $collection)"
                                        :delete="route('collections.destroy', $collection)"
                                        delete-confirm="Delete this collection?"
                                        entity="collections"
                                        :record-id="$collection->id"
                                        :record-name="$collection->internal_name"
                                    />
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $hierarchyMode ? 8 : 7 }}" class="px-4 py-10 text-center text-sm text-gray-500">
                                    No collections found for the current filters.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <x-list.pagination
                :paginator="$collections"
                :action="route('collections.index')"
                :query="$currentQuery"
                :current-per-page="$listState->perPage"
                entity="collections"
            />
        </div>
    </x-layout.index-page>
@endsection
