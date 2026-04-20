@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <x-entity.header entity="items" title="Items">
        @can(\App\Enums\Permission::CREATE_DATA->value)
            <x-ui.button :href="route('items.create')" variant="primary" entity="items" icon="plus">
                Add Item
            </x-ui.button>
        @endcan
    </x-entity.header>

    @if(session('status'))
        <x-ui.alert :message="session('status')" type="success" entity="items" />
    @endif

    <div class="space-y-4">
        {{-- Hierarchy / Flat toggle --}}
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                @if($hierarchyMode && $parentId)
                    <a href="{{ request()->fullUrlWithQuery(['parent_id' => '', 'page' => 1]) }}"
                       class="inline-flex items-center gap-1 text-sm text-gray-600 hover:text-gray-900">
                        <x-heroicon-o-arrow-left class="w-4 h-4" />
                        Back
                    </a>
                @endif
            </div>
            @if($hierarchyMode)
                <a href="{{ request()->fullUrlWithQuery(['hierarchy' => '0', 'parent_id' => '', 'page' => 1]) }}"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md text-sm font-medium border bg-teal-50 border-teal-300 text-teal-800 hover:bg-teal-100">
                    <x-heroicon-o-queue-list class="w-4 h-4" />
                    Hierarchy
                </a>
            @else
                <a href="{{ request()->fullUrlWithQuery(['hierarchy' => '1', 'parent_id' => '', 'page' => 1]) }}"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md text-sm font-medium border bg-gray-50 border-gray-300 text-gray-700 hover:bg-gray-100">
                    <x-heroicon-o-bars-3 class="w-4 h-4" />
                    Flat
                </a>
            @endif
        </div>

        {{-- Breadcrumbs --}}
        @if($hierarchyMode && !empty($breadcrumbs))
            <nav class="flex items-center text-sm text-gray-500" aria-label="Breadcrumb">
                <a href="{{ request()->fullUrlWithQuery(['parent_id' => '', 'page' => 1]) }}"
                   class="hover:text-gray-700">All Items</a>
                @foreach($breadcrumbs as $crumb)
                    <x-heroicon-o-chevron-right class="w-4 h-4 mx-1 shrink-0" />
                    @if($loop->last)
                        <span class="font-medium text-gray-900">{{ $crumb->internal_name }}</span>
                    @else
                        <a href="{{ request()->fullUrlWithQuery(['parent_id' => $crumb->id, 'hierarchy' => '1', 'page' => 1]) }}"
                           class="hover:text-gray-700">{{ $crumb->internal_name }}</a>
                    @endif
                @endforeach
            </nav>
        @endif

        <x-table.filter-bar name="q" placeholder="Search internal name...">
            {{-- Type Filter --}}
            <div class="flex items-center gap-2">
                <label for="typeFilter" class="text-sm text-gray-700">Type:</label>
                <select name="type" id="typeFilter"
                        class="rounded-md border-gray-300 focus:border-teal-500 focus:ring-teal-500 text-sm"
                        onchange="this.form.submit()">
                    <option value="" @selected($type === '')>All Types</option>
                    <option value="object" @selected($type === 'object')>Object</option>
                    <option value="monument" @selected($type === 'monument')>Monument</option>
                    <option value="detail" @selected($type === 'detail')>Detail</option>
                    <option value="picture" @selected($type === 'picture')>Picture</option>
                </select>
            </div>

            {{-- Tag Filter --}}
            @if($availableTags->isNotEmpty())
                <div class="flex items-center gap-2">
                    <label for="tagFilter" class="text-sm text-gray-700">Tags:</label>
                    <select name="tags[]" id="tagFilter" multiple
                            class="rounded-md border-gray-300 focus:border-teal-500 focus:ring-teal-500 text-sm"
                            onchange="this.form.submit()">
                        @foreach($availableTags as $tag)
                            <option value="{{ $tag->id }}" @selected(in_array($tag->id, $tagIds))>
                                {{ $tag->internal_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif
        </x-table.filter-bar>

        <div class="bg-white shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <x-table.header>
                    <x-table.sortable-header field="internal_name" label="Internal Name" />
                    <x-table.header-cell hidden="hidden md:table-cell">Backward Compatibility</x-table.header-cell>
                    @if($hierarchyMode)
                        <x-table.header-cell hidden="hidden md:table-cell">Children</x-table.header-cell>
                    @endif
                    <x-table.sortable-header field="created_at" label="Created" class="hidden lg:table-cell" />
                    <x-table.sortable-header field="updated_at" label="Updated" class="hidden lg:table-cell" />
                    <x-table.header-cell hidden="hidden sm:table-cell">
                        <span class="sr-only">Actions</span>
                    </x-table.header-cell>
                </x-table.header>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($items as $item)
                        <tr class="hover:bg-gray-50 cursor-pointer" onclick="window.location='{{ route('items.show', $item) }}'">
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                <div class="flex items-center gap-2">
                                    {{ $item->internal_name }}
                                    @if($hierarchyMode && $item->children_count > 0)
                                        <a
                                            href="{{ request()->fullUrlWithQuery(['parent_id' => $item->id, 'hierarchy' => '1', 'page' => 1]) }}"
                                            class="inline-flex items-center text-teal-600 hover:text-teal-800"
                                            title="Browse children"
                                            onclick="event.stopPropagation()"
                                        >
                                            <x-heroicon-o-chevron-right class="w-4 h-4" />
                                        </a>
                                    @endif
                                </div>
                            </td>
                            <td class="hidden md:table-cell px-4 py-3 text-sm text-gray-500">{{ $item->backward_compatibility ?? '—' }}</td>
                            @if($hierarchyMode)
                                <td class="hidden md:table-cell px-4 py-3 text-sm text-gray-500">
                                    @if($item->children_count > 0)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-teal-100 text-teal-800">
                                            {{ $item->children_count }}
                                        </span>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                            @endif
                            <td class="hidden lg:table-cell px-4 py-3 text-xs text-gray-400">{{ optional($item->created_at)->format('Y-m-d H:i') }}</td>
                            <td class="hidden lg:table-cell px-4 py-3 text-xs text-gray-400">{{ optional($item->updated_at)->format('Y-m-d H:i') }}</td>
                            <td class="hidden sm:table-cell px-4 py-3 text-right text-sm" onclick="event.stopPropagation()">
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
                            <td colspan="{{ $hierarchyMode ? 6 : 5 }}" class="px-4 py-8 text-center text-sm text-gray-500">No items found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div>
            <x-layout.pagination
                :paginator="$items"
                entity="items"
                param-page="page"
            />
        </div>

        <x-table.delete-modal />
    </div>
</div>
@endsection
