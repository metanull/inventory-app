@extends('layouts.app')

@section('content')
    <x-layout.show-page 
        entity="glossary"
        title="Glossary Entry Detail"
        :back-route="route('glossaries.index')"
        :edit-route="route('glossaries.edit', $glossary)"
        :delete-route="route('glossaries.destroy', $glossary)"
        delete-confirm="Are you sure you want to delete this glossary entry?"
        :backward-compatibility="$glossary->backward_compatibility"
    >
        @if(session('status'))
            <x-ui.alert :message="session('status')" type="success" entity="glossary" />
        @endif

        <x-display.description-list>
            <x-display.field label="Internal Name" :value="$glossary->internal_name" />
        </x-display.description-list>

        <!-- Translations Section -->
        <x-entity.translations-section 
            entity="glossary" 
            :model="$glossary" 
            translationRoute="glossaries.translations" 
            :groupByContext="false"
            primaryField="definition"
            :secondaryField="null"
            :descriptionField="null"
        />

        <!-- Spellings Section -->
        <x-entity.spellings-section :model="$glossary" />

        <!-- Synonyms Section -->
        <x-entity.synonyms-section :model="$glossary" />

        <!-- System Properties -->
        <x-system-properties 
            :id="$glossary->id"
            :backward-compatibility-id="$glossary->backward_compatibility"
            :created-at="$glossary->created_at"
            :updated-at="$glossary->updated_at"
        />
    </x-layout.show-page>
@endsection
