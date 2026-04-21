@extends('layouts.app')

@section('content')
    <x-layout.index-page
        entity="item-images"
        title="Images"
        :create-route="route('items.item-images.create', $item)"
        create-button-text="Attach Image"
    >
        @php
            $currentQuery = $listState->query();
            $sortQuery = $listState->query(['sort', 'direction', 'page']);
        @endphp

        <x-list.parent-context-header
            :breadcrumbs="[
                ['label' => 'Items', 'url' => route('items.index')],
                ['label' => $item->internal_name, 'url' => route('items.show', $item)],
                ['label' => 'Images'],
            ]"
            title="Images"
            parent-label="Item"
            :parent-value="$item->internal_name"
            :parent-url="route('items.show', $item)"
            :back-url="route('items.show', $item)"
            back-label="Back to Item"
        />

        <div class="space-y-6">
            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <x-list.search-form
                    :action="route('items.item-images.index', $item)"
                    :query="$listState->query(['q', 'page'])"
                    :search="$listState->search"
                    placeholder="Search images..."
                    :clear-url="route('items.item-images.index', $item)"
                />
            </div>

            @if ($itemImages->isEmpty())
                <x-ui.empty-state
                    icon="photo"
                    title="No images"
                    description="Attach images to this item to see them here."
                />
            @else
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5">
                    @foreach ($itemImages as $itemImage)
                        <div class="group relative overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                            <div class="aspect-square overflow-hidden bg-gray-100">
                                <img
                                    src="{{ route('items.item-images.view', [$item, $itemImage]) }}"
                                    alt="{{ $itemImage->alt_text ?? $itemImage->original_name }}"
                                    class="h-full w-full object-cover transition-transform duration-200 group-hover:scale-105"
                                    loading="lazy"
                                >
                            </div>
                            <div class="p-2">
                                <p class="truncate text-xs text-gray-600" title="{{ $itemImage->original_name }}">
                                    {{ $itemImage->original_name }}
                                </p>
                                @if ($itemImage->alt_text)
                                    <p class="mt-0.5 truncate text-xs text-gray-400" title="{{ $itemImage->alt_text }}">
                                        {{ $itemImage->alt_text }}
                                    </p>
                                @endif
                            </div>
                            <div class="absolute inset-0 flex items-center justify-center gap-2 bg-black/50 opacity-0 transition-opacity duration-200 group-hover:opacity-100">
                                <x-ui.button
                                    href="{{ route('items.item-images.edit', [$item, $itemImage]) }}"
                                    variant="secondary"
                                    size="sm"
                                >
                                    Edit
                                </x-ui.button>
                                <form method="POST" action="{{ route('items.item-images.destroy', [$item, $itemImage]) }}" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <x-ui.button type="submit" variant="danger" size="sm" onclick="return confirm('Delete this image?')">
                                        Delete
                                    </x-ui.button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>

                <x-list.pagination
                    :paginator="$itemImages"
                    :action="route('items.item-images.index', $item)"
                    :query="$currentQuery"
                    :current-per-page="$listState->perPage"
                    entity="item-images"
                />
            @endif

            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                <h3 class="mb-4 text-sm font-semibold text-gray-700">Upload New Image</h3>
                <x-image-attachment.upload-zone
                    :action="route('images.store')"
                    :success-redirect="route('items.item-images.index', $item)"
                />
            </div>
        </div>
    </x-layout.index-page>
@endsection
