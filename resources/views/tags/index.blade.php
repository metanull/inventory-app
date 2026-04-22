@extends('layouts.app')

@section('content')
    <x-layout.index-page entity="tags">
        @php
            $currentQuery = $listState->query();
            $formQuery = $listState->query(['q', 'page']);
            $sortQuery = $listState->query(['sort', 'direction', 'page']);
        @endphp

        <div class="space-y-6">
            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <x-list.search-form
                    :action="route('tags.index')"
                    :query="$formQuery"
                    :search="$listState->search"
                    placeholder="Search tags..."
                    :clear-url="route('tags.index')"
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
                            :url="route('tags.index')"
                            :query="$sortQuery"
                        />
                        <x-list.sort-link
                            label="Description"
                            field="description"
                            :current-sort="$listState->sort"
                            :current-direction="$listState->direction"
                            :url="route('tags.index')"
                            :query="$sortQuery"
                            class="hidden md:table-cell"
                        />
                        <x-table.header-cell hidden="hidden md:table-cell">Items</x-table.header-cell>
                        <x-table.header-cell hidden="hidden lg:table-cell">Legacy ID</x-table.header-cell>
                        <x-list.sort-link
                            label="Created"
                            field="created_at"
                            :current-sort="$listState->sort"
                            :current-direction="$listState->direction"
                            :url="route('tags.index')"
                            :query="$sortQuery"
                            class="hidden lg:table-cell"
                        />
                        <x-table.header-cell hidden="hidden sm:table-cell">
                            <span class="sr-only">Actions</span>
                        </x-table.header-cell>
                    </x-table.header>

                    <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse($tags as $tag)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                    <a href="{{ route('tags.show', $tag) }}" class="text-fuchsia-700 hover:text-fuchsia-900 hover:underline">
                                        {{ $tag->internal_name }}
                                    </a>
                                </td>
                                <td class="hidden md:table-cell px-4 py-3 text-sm text-gray-500">{{ \Illuminate\Support\Str::limit($tag->description, 100) }}</td>
                                <td class="hidden md:table-cell px-4 py-3 text-sm">
                                    @if($tag->items_count > 0)
                                        <a
                                            href="{{ route('items.index', ['hierarchy' => false, 'tags' => [$tag->id]]) }}"
                                            class="inline-flex items-center text-blue-600 hover:text-blue-800"
                                            title="View items with this tag"
                                        >
                                            {{ $tag->items_count }} {{ \Illuminate\Support\Str::plural('item', $tag->items_count) }}
                                            <x-heroicon-o-arrow-top-right-on-square class="ml-1 h-4 w-4" />
                                        </a>
                                    @else
                                        <span class="text-gray-400">0 items</span>
                                    @endif
                                </td>
                                <td class="hidden lg:table-cell px-4 py-3 text-sm text-gray-500">{{ $tag->backward_compatibility ?? '—' }}</td>
                                <td class="hidden lg:table-cell px-4 py-3 text-xs text-gray-400">{{ optional($tag->created_at)->format('Y-m-d H:i') }}</td>
                                <td class="hidden sm:table-cell px-4 py-3 text-right text-sm">
                                    <x-table.row-actions
                                        :view="route('tags.show', $tag)"
                                        :edit="route('tags.edit', $tag)"
                                        :delete="route('tags.destroy', $tag)"
                                        delete-confirm="Delete this tag?"
                                        entity="tag"
                                        :record-id="$tag->id"
                                        :record-name="$tag->internal_name"
                                    />
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-10 text-center text-sm text-gray-500">
                                    No tags found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <x-list.pagination
                :paginator="$tags"
                :action="route('tags.index')"
                :query="$currentQuery"
                :current-per-page="$listState->perPage"
                entity="tags"
            />
        </div>
    </x-layout.index-page>
@endsection
