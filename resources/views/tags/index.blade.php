@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <x-entity.header entity="tags" title="Tags">
        @can(\App\Enums\Permission::CREATE_DATA->value)
            <x-ui.button :href="route('tags.create')" variant="primary" entity="tags" icon="plus">
                Add Tag
            </x-ui.button>
        @endcan
    </x-entity.header>

    @if(session('status'))
        <x-ui.alert :message="session('status')" type="success" entity="tags" />
    @endif

    <div class="space-y-4">
        <x-table.filter-bar name="q" placeholder="Search tags..." />

        <div class="bg-white shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <x-table.header>
                    <x-table.sortable-header field="internal_name" label="Internal Name" />
                    <x-table.header-cell hidden="hidden md:table-cell">
                        Description
                    </x-table.header-cell>
                    <x-table.header-cell hidden="hidden md:table-cell">
                        Items
                    </x-table.header-cell>
                    <x-table.header-cell hidden="hidden lg:table-cell">
                        Legacy ID
                    </x-table.header-cell>
                    <x-table.sortable-header field="created_at" label="Created" class="hidden lg:table-cell" />
                    <x-table.header-cell hidden="hidden sm:table-cell">
                        <span class="sr-only">Actions</span>
                    </x-table.header-cell>
                </x-table.header>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($tags as $tag)
                        <tr class="hover:bg-gray-50 cursor-pointer" onclick="window.location='{{ route('tags.show', $tag) }}'">
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $tag->internal_name }}</td>
                            <td class="hidden md:table-cell px-4 py-3 text-sm text-gray-500">{{ Str::limit($tag->description, 100) }}</td>
                            <td class="hidden md:table-cell px-4 py-3 text-sm" onclick="event.stopPropagation()">
                                @php($itemCount = $tag->items()->count())
                                @if($itemCount > 0)
                                    <a href="{{ route('items.index', ['tags' => [$tag->id]]) }}"
                                       class="inline-flex items-center text-blue-600 hover:text-blue-800"
                                       title="View items with this tag">
                                        {{ $itemCount }} {{ \Illuminate\Support\Str::plural('item', $itemCount) }}
                                        <x-heroicon-o-arrow-top-right-on-square class="w-4 h-4 ml-1" />
                                    </a>
                                @else
                                    <span class="text-gray-400">0 items</span>
                                @endif
                            </td>
                            <td class="hidden lg:table-cell px-4 py-3 text-sm text-gray-500">{{ $tag->backward_compatibility ?? '—' }}</td>
                            <td class="hidden lg:table-cell px-4 py-3 text-xs text-gray-400">{{ optional($tag->created_at)->format('Y-m-d H:i') }}</td>
                            <td class="hidden sm:table-cell px-4 py-3 text-right text-sm" onclick="event.stopPropagation()">
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
                            <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500">No tags found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div>
            <x-layout.pagination
                :paginator="$tags"
                entity="tag"
                param-page="page"
            />
        </div>

        <x-table.delete-modal />
    </div>
</div>
@endsection
