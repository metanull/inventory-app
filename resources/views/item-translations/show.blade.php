@extends('layouts.app')

@section('content')
    <x-layout.show-page 
        entity="item_translations"
        :title="$itemTranslation->name"
        :back-route="route('item-translations.index')"
        :edit-route="route('item-translations.edit', $itemTranslation)"
        :delete-route="route('item-translations.destroy', $itemTranslation)"
        delete-confirm="Are you sure you want to delete this translation?"
        :backward-compatibility="$itemTranslation->backward_compatibility"
    >
        @if(session('success'))
            <x-ui.alert :message="session('success')" type="success" entity="item_translations" />
        @endif

        <x-display.description-list>
            <x-display.field label="Name" :value="$itemTranslation->name" />
            <x-display.field label="Alternate Name" :value="$itemTranslation->alternate_name" />
            <x-display.field label="Item">
                <x-display.item-reference :item="$itemTranslation->item" />
            </x-display.field>
            <x-display.field label="Language">
                <x-display.language-reference :language="$itemTranslation->language" />
            </x-display.field>
            <x-display.field label="Context">
                <x-display.context-reference :context="$itemTranslation->context" />
            </x-display.field>
            <x-display.field label="Description" full-width>
                <x-display.markdown :content="$itemTranslation->description" />
            </x-display.field>
            <x-display.field label="Type" :value="$itemTranslation->type" />
            <x-display.field label="Holder" :value="$itemTranslation->holder" full-width />
            <x-display.field label="Owner" :value="$itemTranslation->owner" />
            <x-display.field label="Initial Owner" :value="$itemTranslation->initial_owner" />
            <x-display.field label="Dates" :value="$itemTranslation->dates" />
            <x-display.field label="Location" :value="$itemTranslation->location" />
            <x-display.field label="Dimensions" :value="$itemTranslation->dimensions" />
            <x-display.field label="Place of Production" :value="$itemTranslation->place_of_production" />
            <x-display.field label="Method for Datation" :value="$itemTranslation->method_for_datation" full-width />
            <x-display.field label="Method for Provenance" :value="$itemTranslation->method_for_provenance" full-width />
            <x-display.field label="Obtention" :value="$itemTranslation->obtention" full-width />
            <x-display.field label="Bibliography" :value="$itemTranslation->bibliography" full-width />
            
            @if($itemTranslation->author)
                <x-display.field label="Author" :value="$itemTranslation->author->name" />
            @endif
            
            @if($itemTranslation->textCopyEditor)
                <x-display.field label="Text Copy Editor" :value="$itemTranslation->textCopyEditor->name" />
            @endif
            
            @if($itemTranslation->translator)
                <x-display.field label="Translator" :value="$itemTranslation->translator->name" />
            @endif
            
            @if($itemTranslation->translationCopyEditor)
                <x-display.field label="Translation Copy Editor" :value="$itemTranslation->translationCopyEditor->name" />
            @endif
            
            @if($itemTranslation->extra)
                <x-display.field label="Extra Data" full-width>
                    <pre class="text-xs bg-gray-50 p-2 rounded">{{ json_encode($itemTranslation->extra, JSON_PRETTY_PRINT) }}</pre>
                </x-display.field>
            @endif
        </x-display.description-list>

        <!-- System Properties -->
        <x-system-properties 
            :id="$itemTranslation->id"
            :backward-compatibility-id="$itemTranslation->backward_compatibility"
            :created-at="$itemTranslation->created_at"
            :updated-at="$itemTranslation->updated_at"
        />
    </x-layout.show-page>
@endsection
