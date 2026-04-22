@extends('layouts.app')

@section('content')
    <x-layout.index-page
        entity="partner-images"
        title="Images"
        :create-route="route('partners.partner-images.create', $partner)"
        create-button-text="Attach Image"
    >
        @php
            $currentQuery = $listState->query();
        @endphp

        <x-list.parent-context-header
            :breadcrumbs="[
                ['label' => 'Partners', 'url' => route('partners.index')],
                ['label' => $partner->internal_name, 'url' => route('partners.show', $partner)],
                ['label' => 'Images'],
            ]"
            title="Images"
            parent-label="Partner"
            :parent-value="$partner->internal_name"
            :parent-url="route('partners.show', $partner)"
            :back-url="route('partners.show', $partner)"
            back-label="Back to Partner"
        />

        <div class="space-y-6">
            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <x-list.search-form
                    :action="route('partners.partner-images.index', $partner)"
                    :query="$listState->query(['q', 'page'])"
                    :search="$listState->search"
                    placeholder="Search images..."
                    :clear-url="route('partners.partner-images.index', $partner)"
                />
            </div>

            @if ($partnerImages->isEmpty())
                <x-ui.empty-state
                    icon="photo"
                    title="No images"
                    description="Attach images to this partner to see them here."
                />
            @else
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5">
                    @foreach ($partnerImages as $partnerImage)
                        <div class="group relative overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                            <div class="aspect-square overflow-hidden bg-gray-100">
                                <img
                                    src="{{ route('partners.partner-images.view', [$partner, $partnerImage]) }}"
                                    alt="{{ $partnerImage->alt_text ?? $partnerImage->original_name }}"
                                    class="h-full w-full object-cover transition-transform duration-200 group-hover:scale-105"
                                    loading="lazy"
                                >
                            </div>
                            <div class="p-2">
                                <p class="truncate text-xs text-gray-600" title="{{ $partnerImage->original_name }}">
                                    {{ $partnerImage->original_name }}
                                </p>
                                @if ($partnerImage->alt_text)
                                    <p class="mt-0.5 truncate text-xs text-gray-400" title="{{ $partnerImage->alt_text }}">
                                        {{ $partnerImage->alt_text }}
                                    </p>
                                @endif
                            </div>
                            <div class="absolute inset-0 flex items-center justify-center gap-2 bg-black/50 opacity-0 transition-opacity duration-200 group-hover:opacity-100">
                                <x-ui.button
                                    href="{{ route('partners.partner-images.edit', [$partner, $partnerImage]) }}"
                                    variant="secondary"
                                    size="sm"
                                >
                                    Edit
                                </x-ui.button>
                                <form method="POST" action="{{ route('partners.partner-images.destroy', [$partner, $partnerImage]) }}" class="inline">
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
                    :paginator="$partnerImages"
                    :action="route('partners.partner-images.index', $partner)"
                    :query="$currentQuery"
                    :current-per-page="$listState->perPage"
                    entity="partner-images"
                />
            @endif

            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                <h3 class="mb-4 text-sm font-semibold text-gray-700">Upload New Image</h3>
                <x-image-attachment.upload-zone
                    :action="route('images.store')"
                    :success-redirect="route('partners.partner-images.index', $partner)"
                />
            </div>
        </div>
    </x-layout.index-page>
@endsection
