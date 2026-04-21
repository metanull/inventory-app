@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <x-entity.header entity="available-images" title="Available Images" />

    @if(session('success'))
        <x-ui.alert :message="session('success')" type="success" entity="available-images" />
    @endif

    @php
        $currentQuery = $listState->query();
        $formQuery = $listState->query(['q', 'page']);
        $sortQuery = $listState->query(['sort', 'direction', 'page']);
    @endphp

    <div class="space-y-6">
        <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
            <x-list.search-form
                :action="route('available-images.index')"
                :query="$formQuery"
                :search="$listState->search"
                placeholder="Search by filename or comment..."
                :clear-url="route('available-images.index')"
            />
        </div>

        <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-gray-200">
                <x-table.header>
                    <x-list.sort-link
                        label="Filename"
                        field="path"
                        :current-sort="$listState->sort"
                        :current-direction="$listState->direction"
                        :url="route('available-images.index')"
                        :query="$sortQuery"
                    />
                    <x-table.header-cell hidden="hidden md:table-cell">Comment</x-table.header-cell>
                    <x-list.sort-link
                        label="Created"
                        field="created_at"
                        :current-sort="$listState->sort"
                        :current-direction="$listState->direction"
                        :url="route('available-images.index')"
                        :query="$sortQuery"
                        class="hidden lg:table-cell"
                    />
                    <x-table.header-cell hidden="hidden sm:table-cell">
                        <span class="sr-only">Actions</span>
                    </x-table.header-cell>
                </x-table.header>

                <tbody class="divide-y divide-gray-200 bg-white">
                    @forelse($availableImages as $image)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                <a href="{{ route('available-images.show', $image) }}" class="text-teal-700 hover:text-teal-900 hover:underline">
                                    {{ basename($image->path) }}
                                </a>
                            </td>
                            <td class="hidden md:table-cell px-4 py-3 text-sm text-gray-600">{{ $image->comment ?? '—' }}</td>
                            <td class="hidden lg:table-cell px-4 py-3 text-xs text-gray-400">{{ optional($image->created_at)->format('Y-m-d H:i') }}</td>
                            <td class="hidden sm:table-cell px-4 py-3 text-right text-sm">
                                <x-table.row-actions
                                    :view="route('available-images.show', $image)"
                                    :edit="route('available-images.edit', $image)"
                                    :delete="route('available-images.destroy', $image)"
                                    delete-confirm="Delete this image?"
                                    entity="available-images"
                                    :record-id="$image->id"
                                    :record-name="basename($image->path)"
                                />
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-10 text-center text-sm text-gray-500">
                                No images found for the current filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <x-list.pagination
            :paginator="$availableImages"
            :action="route('available-images.index')"
            :query="$currentQuery"
            :current-per-page="$listState->perPage"
            entity="available-images"
        />
    </div>
</div>
@endsection
