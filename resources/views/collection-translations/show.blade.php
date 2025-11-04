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

        <!-- Parent Entity Section -->
        <x-display.parent-entity 
            :parentEntity="$collectionTranslation->collection"
            parentType="collection"
            :showRoute="route('collections.show', $collectionTranslation->collection)"
            :editRoute="route('collections.edit', $collectionTranslation->collection)"
        />

        <x-display.description-list>
            <x-display.field label="Title" :value="$collectionTranslation->title" />
            <x-display.field label="Language">
                <x-display.language-reference :language="$collectionTranslation->language" />
            </x-display.field>
            <x-display.field label="Context">
                <x-display.context-reference :context="$collectionTranslation->context" />
            </x-display.field>
            <x-display.field label="Description" full-width>
                <x-display.markdown :content="$collectionTranslation->description" />
            </x-display.field>
            <x-display.field label="URL" :value="$collectionTranslation->url" full-width />
            
            @if($collectionTranslation->extra)
                <x-display.field label="Metadata" full-width>
                    <x-display.key-value :data="$collectionTranslation->extra_decoded" />
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
