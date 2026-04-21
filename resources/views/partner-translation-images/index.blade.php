@extends('layouts.app')

@section('content')
    <x-layout.index-page
        entity="partner-translation-images"
        title="Images"
        :create-route="route('partner-translations.partner-translation-images.create', $partnerTranslation)"
        create-button-text="Attach Image"
    >
        @php
            $currentQuery = $listState->query();
        @endphp

        <x-list.parent-context-header
            :breadcrumbs="[
                ['label' => 'Partners', 'url' => route('partners.index')],
                ['label' => $partner->internal_name, 'url' => route('partners.show', $partner)],
                ['label' => 'Translations', 'url' => route('partner-translations.index', ['partner_id' => $partner->id])],
                ['label' => $partnerTranslation->name, 'url' => route('partner-translations.show', $partnerTranslation)],
                ['label' => 'Images'],
            ]"
            title="Images"
            parent-label="Translation"
            :parent-value="$partnerTranslation->name"
            :parent-url="route('partner-translations.show', $partnerTranslation)"
            :back-url="route('partner-translations.show', $partnerTranslation)"
            back-label="Back to Translation"
        />

        <div class="space-y-6">
            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <x-list.search-form
                    :action="route('partner-translations.partner-translation-images.index', $partnerTranslation)"
                    :query="$listState->query(['q', 'page'])"
                    :search="$listState->search"
                    placeholder="Search images..."
                    :clear-url="route('partner-translations.partner-translation-images.index', $partnerTranslation)"
                />
            </div>

            @if ($partnerTranslationImages->isEmpty())
                <x-ui.empty-state
                    icon="photo"
                    title="No images"
                    description="Attach images to this translation to see them here."
                />
            @else
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5">
                    @foreach ($partnerTranslationImages as $partnerTranslationImage)
                        <div class="group relative overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                            <div class="aspect-square overflow-hidden bg-gray-100">
                                <img
                                    src="{{ route('partner-translations.partner-translation-images.view', [$partnerTranslation, $partnerTranslationImage]) }}"
                                    alt="{{ $partnerTranslationImage->alt_text ?? $partnerTranslationImage->original_name }}"
                                    class="h-full w-full object-cover transition-transform duration-200 group-hover:scale-105"
                                    loading="lazy"
                                >
                            </div>
                            <div class="p-2">
                                <p class="truncate text-xs text-gray-600" title="{{ $partnerTranslationImage->original_name }}">
                                    {{ $partnerTranslationImage->original_name }}
                                </p>
                                @if ($partnerTranslationImage->alt_text)
                                    <p class="mt-0.5 truncate text-xs text-gray-400" title="{{ $partnerTranslationImage->alt_text }}">
                                        {{ $partnerTranslationImage->alt_text }}
                                    </p>
                                @endif
                            </div>
                            <div class="absolute inset-0 flex items-center justify-center gap-2 bg-black/50 opacity-0 transition-opacity duration-200 group-hover:opacity-100">
                                <x-ui.button
                                    href="{{ route('partner-translations.partner-translation-images.edit', [$partnerTranslation, $partnerTranslationImage]) }}"
                                    variant="secondary"
                                    size="sm"
                                >
                                    Edit
                                </x-ui.button>
                                <form method="POST" action="{{ route('partner-translations.partner-translation-images.destroy', [$partnerTranslation, $partnerTranslationImage]) }}" class="inline">
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
                    :paginator="$partnerTranslationImages"
                    :action="route('partner-translations.partner-translation-images.index', $partnerTranslation)"
                    :query="$currentQuery"
                    :current-per-page="$listState->perPage"
                    entity="partner-translation-images"
                />
            @endif

            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                <h3 class="mb-4 text-sm font-semibold text-gray-700">Upload New Image</h3>
                <x-image-attachment.upload-zone
                    :action="route('images.store')"
                    :success-redirect="route('partner-translations.partner-translation-images.index', $partnerTranslation)"
                />
            </div>
        </div>
    </x-layout.index-page>
@endsection
