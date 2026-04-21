@extends('layouts.app')

@section('content')
    <x-layout.index-page
        entity="collection-images"
        title="Images"
        :create-route="route('collections.collection-images.create', $collection)"
        create-button-text="Attach Image"
    >
        @php
            $currentQuery = $listState->query();
        @endphp

        <x-list.parent-context-header
            :breadcrumbs="[
                ['label' => 'Collections', 'url' => route('collections.index')],
                ['label' => $collection->internal_name, 'url' => route('collections.show', $collection)],
                ['label' => 'Images'],
            ]"
            title="Images"
            parent-label="Collection"
            :parent-value="$collection->internal_name"
            :parent-url="route('collections.show', $collection)"
            :back-url="route('collections.show', $collection)"
            back-label="Back to Collection"
        />

        <div class="space-y-6">
            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <x-list.search-form
                    :action="route('collections.collection-images.index', $collection)"
                    :query="$listState->query(['q', 'page'])"
                    :search="$listState->search"
                    placeholder="Search images..."
                    :clear-url="route('collections.collection-images.index', $collection)"
                />
            </div>

            @if ($collectionImages->isEmpty())
                <x-ui.empty-state
                    icon="photo"
                    title="No images"
                    description="Attach images to this collection to see them here."
                />
            @else
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5">
                    @foreach ($collectionImages as $collectionImage)
                        <div class="group relative overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                            <div class="aspect-square overflow-hidden bg-gray-100">
                                <img
                                    src="{{ route('collections.collection-images.view', [$collection, $collectionImage]) }}"
                                    alt="{{ $collectionImage->alt_text ?? $collectionImage->original_name }}"
                                    class="h-full w-full object-cover transition-transform duration-200 group-hover:scale-105"
                                    loading="lazy"
                                >
                            </div>
                            <div class="p-2">
                                <p class="truncate text-xs text-gray-600" title="{{ $collectionImage->original_name }}">
                                    {{ $collectionImage->original_name }}
                                </p>
                                @if ($collectionImage->alt_text)
                                    <p class="mt-0.5 truncate text-xs text-gray-400" title="{{ $collectionImage->alt_text }}">
                                        {{ $collectionImage->alt_text }}
                                    </p>
                                @endif
                            </div>
                            <div class="absolute inset-0 flex items-center justify-center gap-2 bg-black/50 opacity-0 transition-opacity duration-200 group-hover:opacity-100">
                                <x-ui.button
                                    href="{{ route('collections.collection-images.edit', [$collection, $collectionImage]) }}"
                                    variant="secondary"
                                    size="sm"
                                >
                                    Edit
                                </x-ui.button>
                                <form method="POST" action="{{ route('collections.collection-images.destroy', [$collection, $collectionImage]) }}" class="inline">
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
                    :paginator="$collectionImages"
                    :action="route('collections.collection-images.index', $collection)"
                    :query="$currentQuery"
                    :current-per-page="$listState->perPage"
                    entity="collection-images"
                />
            @endif

            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                <h3 class="mb-4 text-sm font-semibold text-gray-700">Upload New Image</h3>
                <x-image-attachment.upload-zone
                    :action="route('images.store')"
                    :success-redirect="route('collections.collection-images.index', $collection)"
                />
            </div>
        </div>
    </x-layout.index-page>
@endsection
