@extends('layouts.app')

@section('content')
    <x-layout.show-page 
        entity="collection_translations"
        :title="$collectionTranslation->title"
        :back-route="route('collection-translations.index')"
        :edit-route="route('collection-translations.edit', $collectionTranslation)"
        :delete-route="route('collection-translations.destroy', $collectionTranslation)"
        delete-confirm="Are you sure you want to delete this translation?"
        :backward-compatibility="$collectionTranslation->backward_compatibility"
    >
        @if(session('success'))
            <x-ui.alert :message="session('success')" type="success" entity="collection_translations" />
        @endif

        <x-display.description-list>
            <x-display.field label="Title" :value="$collectionTranslation->title" />
            <x-display.field label="Collection">
                @if($collectionTranslation->collection)
                    <a href="{{ route('collections.show', $collectionTranslation->collection) }}" class="text-indigo-600 hover:text-indigo-900">
                        {{ $collectionTranslation->collection->internal_name }}
                    </a>
                @else
                    <span class="text-gray-400">N/A</span>
                @endif
            </x-display.field>
            <x-display.field label="Language">
                @if($collectionTranslation->language)
                    <a href="{{ route('languages.show', $collectionTranslation->language) }}" class="text-indigo-600 hover:text-indigo-900">
                        {{ $collectionTranslation->language->internal_name }}
                    </a>
                @else
                    <span class="text-gray-400">{{ $collectionTranslation->language_id }}</span>
                @endif
            </x-display.field>
            <x-display.field label="Context">
                @if($collectionTranslation->context)
                    <a href="{{ route('contexts.show', $collectionTranslation->context) }}" class="text-indigo-600 hover:text-indigo-900">
                        {{ $collectionTranslation->context->internal_name }}
                        @if($collectionTranslation->context->is_default)
                            <span class="ml-2 inline-flex px-2 py-0.5 rounded text-xs bg-emerald-100 text-emerald-700">default</span>
                        @endif
                    </a>
                @else
                    <span class="text-gray-400">N/A</span>
                @endif
            </x-display.field>
            <x-display.field label="Description" :value="$collectionTranslation->description" full-width />
            <x-display.field label="URL" :value="$collectionTranslation->url" full-width />
            
            @if($collectionTranslation->extra)
                <x-display.field label="Extra Data" full-width>
                    <pre class="text-xs bg-gray-50 p-2 rounded">{{ json_encode($collectionTranslation->extra, JSON_PRETTY_PRINT) }}</pre>
                </x-display.field>
            @endif
        </x-display.description-list>

        <!-- System Properties -->
        <x-system-properties 
            :id="$collectionTranslation->id"
            :backward-compatibility-id="$collectionTranslation->backward_compatibility"
            :created-at="$collectionTranslation->created_at"
            :updated-at="$collectionTranslation->updated_at"
        />
    </x-layout.show-page>
@endsection
