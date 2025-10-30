@extends('layouts.app')

@section('content')
<x-layout.show-page 
    entity="translation"
    title="Translation Detail"
    :back-route="route('glossaries.translations.index', $glossary)"
    :edit-route="route('glossaries.translations.edit', [$glossary, $translation])"
    :delete-route="route('glossaries.translations.destroy', [$glossary, $translation])"
    delete-confirm="Are you sure you want to delete this translation?"
>
    @if(session('success'))
        <x-ui.alert :message="session('success')" type="success" entity="translation" />
    @endif

    <x-display.description-list>
        <x-display.field label="Glossary Entry" :value="$glossary->internal_name" />
        <x-display.field label="Language" :value="$translation->language->internal_name" />
        <x-display.field label="Definition">
            <x-display.markdown :content="$translation->definition" />
        </x-display.field>
    </x-display.description-list>
</x-layout.show-page>
@endsection
