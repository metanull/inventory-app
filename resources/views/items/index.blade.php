@extends('layouts.app')

@section('content')
    <x-layout.index-page entity="items">
        @php
            $cleanQuery = static fn (array $query): array => array_filter(
                $query,
                static fn (mixed $value): bool => $value !== null && $value !== [] && $value !== ''
            );
            $currentQuery = $listState->query();
            $formQuery = $listState->query(['q', 'page']);
            $sortQuery = $listState->query(['sort', 'direction', 'page']);
            $hierarchyQuery = $listState->query(['page', 'parent_id']);
            $flatQuery = $listState->query(['page', 'parent_id', 'hierarchy']);
            $selectedTagIds = $listState->filters['tags'] ?? [];
        @endphp

        <div class="space-y-6">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="inline-flex overflow-hidden rounded-md border border-gray-200 bg-white shadow-sm">
                    <a
                        href="{{ route('items.index', array_merge($hierarchyQuery, ['hierarchy' => true])) }}"
                        class="px-4 py-2 text-sm font-medium {{ $hierarchyMode ? 'bg-teal-600 text-white' : 'text-gray-700 hover:bg-gray-50' }}"
                    >
                        Hierarchy
                    </a>
                    <a
                        href="{{ route('items.index', array_merge($flatQuery, ['hierarchy' => false])) }}"
                        class="px-4 py-2 text-sm font-medium {{ ! $hierarchyMode ? 'bg-teal-600 text-white' : 'text-gray-700 hover:bg-gray-50' }}"
                    >
                        Flat
                    </a>
                </div>

                @if($hierarchyMode && $parentItem)
                    <x-ui.button
                        :href="route('items.index', $cleanQuery(array_merge($hierarchyQuery, ['hierarchy' => true, 'parent_id' => $parentItem->parent_id])))"
                        variant="secondary"
                        size="sm"
                    >
                        Back
                    </x-ui.button>
                @endif
            </div>

            @if($hierarchyMode)
                <nav class="flex flex-wrap items-center gap-1 text-sm text-gray-500" aria-label="Breadcrumb">
                    <a href="{{ route('items.index', array_merge($hierarchyQuery, ['hierarchy' => true])) }}" class="hover:text-teal-700">All Items</a>
                    @foreach($breadcrumbs as $crumb)
                        <x-heroicon-o-chevron-right class="h-4 w-4 shrink-0" />
                        @if($loop->last)
                            <span class="font-medium text-gray-900">{{ $crumb['label'] }}</span>
                        @else
                            <a
                                href="{{ route('items.index', $cleanQuery(array_merge($hierarchyQuery, ['hierarchy' => true, 'parent_id' => $crumb['id']]))) }}"
                                class="hover:text-teal-700"
                            >
                                {{ $crumb['label'] }}
                            </a>
                        @endif
                    @endforeach
                </nav>
            @endif

            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <x-list.search-form
                    :action="route('items.index')"
                    :query="$formQuery"
                    :search="$listState->search"
                    placeholder="Search internal names, legacy references, and translations..."
                    :clear-url="route('items.index')"
                    class="gap-4"
                >
                    <div class="w-full md:w-44">
                        <label for="type" class="mb-1 block text-xs font-medium uppercase tracking-wide text-gray-500">Type</label>
                        <select id="type" name="type" class="block w-full rounded-md border-gray-300 text-sm focus:border-teal-500 focus:ring-teal-500">
                            <option value="">All types</option>
                            <option value="object" @selected(($listState->filters['type'] ?? null) === 'object')>Object</option>
                            <option value="monument" @selected(($listState->filters['type'] ?? null) === 'monument')>Monument</option>
                            <option value="detail" @selected(($listState->filters['type'] ?? null) === 'detail')>Detail</option>
                            <option value="picture" @selected(($listState->filters['type'] ?? null) === 'picture')>Picture</option>
                        </select>
                    </div>

                    <div class="w-full md:w-52">
                        <label class="mb-1 block text-xs font-medium uppercase tracking-wide text-gray-500">Partner</label>
                        <x-form.entity-select
                            name="partner_id"
                            :modelClass="\App\Models\Partner::class"
                            displayField="internal_name"
                            :value="$selectedPartner?->id"
                            entity="partners"
                            placeholder="All partners"
                        />
                    </div>

                    <div class="w-full md:w-52">
                        <label class="mb-1 block text-xs font-medium uppercase tracking-wide text-gray-500">Collection</label>
                        <x-form.entity-select
                            name="collection_id"
                            :modelClass="\App\Models\Collection::class"
                            displayField="internal_name"
                            :value="$selectedCollection?->id"
                            entity="collections"
                            placeholder="All collections"
                        />
                    </div>

                    <div class="w-full md:w-52">
                        <label class="mb-1 block text-xs font-medium uppercase tracking-wide text-gray-500">Project</label>
                        <x-form.entity-select
                            name="project_id"
                            :modelClass="\App\Models\Project::class"
                            displayField="internal_name"
                            :value="$selectedProject?->id"
                            entity="projects"
                            placeholder="All projects"
                        />
                    </div>

                    <div class="w-full md:w-52">
                        <label class="mb-1 block text-xs font-medium uppercase tracking-wide text-gray-500">Country</label>
                        <x-form.entity-select
                            name="country_id"
                            :modelClass="\App\Models\Country::class"
                            displayField="internal_name"
                            :value="$selectedCountry?->id"
                            entity="countries"
                            placeholder="All countries"
                        />
                    </div>

                    <div class="w-full">
                        <label class="mb-1 block text-xs font-medium uppercase tracking-wide text-gray-500">Tags</label>
                        <x-form.searchable-multi-select
                            name="tags"
                            :modelClass="\App\Models\Tag::class"
                            displayField="internal_name"
                            :selected-options="$selectedTags"
                            entity="tags"
                            placeholder="Filter by tags"
                        />
                    </div>
                </x-list.search-form>
            </div>

            @if($selectedTags->isNotEmpty())
                <div class="flex flex-wrap gap-2">
                    @foreach($selectedTags as $tag)
                        @php
                            $removeTagQuery = $listState->query(['page']);
                            $removeTagQuery['tags'] = array_values(array_filter($selectedTagIds, static fn (string $tagId): bool => $tagId !== $tag->id));
                        @endphp

                        <a
                            href="{{ route('items.index', $cleanQuery($removeTagQuery)) }}"
                            class="inline-flex items-center gap-2 rounded-full bg-teal-100 px-3 py-1 text-sm text-teal-800 hover:bg-teal-200"
                        >
                            <span>{{ $tag->description ?: $tag->internal_name }}</span>
                            <x-heroicon-s-x-mark class="h-4 w-4" />
                        </a>
                    @endforeach
                </div>
            @endif

            <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                <table class="min-w-full divide-y divide-gray-200">
                    <x-table.header>
                        <x-list.sort-link
                            label="Internal Name"
                            field="internal_name"
                            :current-sort="$listState->sort"
                            :current-direction="$listState->direction"
                            :url="route('items.index')"
                            :query="$sortQuery"
                        />
                        <x-table.header-cell>Type</x-table.header-cell>
                        <x-table.header-cell hidden="hidden lg:table-cell">Legacy Ref.</x-table.header-cell>
                        <x-table.header-cell hidden="hidden xl:table-cell">Partner</x-table.header-cell>
                        <x-table.header-cell hidden="hidden xl:table-cell">Collection</x-table.header-cell>
                        <x-table.header-cell hidden="hidden xl:table-cell">Country</x-table.header-cell>
                        @if($hierarchyMode)
                            <x-table.header-cell hidden="hidden md:table-cell">Children</x-table.header-cell>
                        @endif
                        <x-list.sort-link
                            label="Created"
                            field="created_at"
                            :current-sort="$listState->sort"
                            :current-direction="$listState->direction"
                            :url="route('items.index')"
                            :query="$sortQuery"
                            class="hidden 2xl:table-cell"
                        />
                        <x-list.sort-link
                            label="Updated"
                            field="updated_at"
                            :current-sort="$listState->sort"
                            :current-direction="$listState->direction"
                            :url="route('items.index')"
                            :query="$sortQuery"
                            class="hidden 2xl:table-cell"
                        />
                        <x-table.header-cell hidden="hidden sm:table-cell">
                            <span class="sr-only">Actions</span>
                        </x-table.header-cell>
                    </x-table.header>

                    <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse($items as $item)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                    <div class="space-y-1">
                                        <a href="{{ route('items.show', $item) }}" class="text-teal-700 hover:text-teal-900 hover:underline">
                                            {{ $item->internal_name }}
                                        </a>

                                        @if($hierarchyMode && $item->children_count > 0)
                                            <div>
                                                <a
                                                    href="{{ route('items.index', $cleanQuery(array_merge($hierarchyQuery, ['hierarchy' => true, 'parent_id' => $item->id]))) }}"
                                                    class="inline-flex items-center gap-1 text-xs text-teal-600 hover:text-teal-800"
                                                >
                                                    <span>Browse children</span>
                                                    <x-heroicon-o-chevron-right class="h-3.5 w-3.5" />
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600">{{ $item->type?->value ?? '—' }}</td>
                                <td class="hidden lg:table-cell px-4 py-3 text-sm text-gray-500">{{ $item->backward_compatibility ?? '—' }}</td>
                                <td class="hidden xl:table-cell px-4 py-3 text-sm text-gray-500">
                                    <x-display.partner-reference :partner="$item->partner" />
                                </td>
                                <td class="hidden xl:table-cell px-4 py-3 text-sm text-gray-500">
                                    <x-display.collection-reference :collection="$item->collection" />
                                </td>
                                <td class="hidden xl:table-cell px-4 py-3 text-sm text-gray-500">
                                    <x-display.country-reference :country="$item->country" />
                                </td>
                                @if($hierarchyMode)
                                    <td class="hidden md:table-cell px-4 py-3 text-sm text-gray-500">
                                        @if($item->children_count > 0)
                                            <span class="inline-flex items-center rounded-full bg-teal-100 px-2 py-0.5 text-xs font-medium text-teal-800">
                                                {{ $item->children_count }}
                                            </span>
                                        @else
                                            <span class="text-gray-400">—</span>
                                        @endif
                                    </td>
                                @endif
                                <td class="hidden 2xl:table-cell px-4 py-3 text-xs text-gray-400">{{ optional($item->created_at)->format('Y-m-d H:i') }}</td>
                                <td class="hidden 2xl:table-cell px-4 py-3 text-xs text-gray-400">{{ optional($item->updated_at)->format('Y-m-d H:i') }}</td>
                                <td class="hidden sm:table-cell px-4 py-3 text-right text-sm">
                                    <x-table.row-actions
                                        :view="route('items.show', $item)"
                                        :edit="route('items.edit', $item)"
                                        :delete="route('items.destroy', $item)"
                                        delete-confirm="Delete this item?"
                                        entity="items"
                                        :record-id="$item->id"
                                        :record-name="$item->internal_name"
                                    />
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $hierarchyMode ? 10 : 9 }}" class="px-4 py-10 text-center text-sm text-gray-500">
                                    No items found for the current filters.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <x-list.pagination
                :paginator="$items"
                :action="route('items.index')"
                :query="$currentQuery"
                :current-per-page="$listState->perPage"
                entity="items"
            />
        </div>
    </x-layout.index-page>
@endsection
