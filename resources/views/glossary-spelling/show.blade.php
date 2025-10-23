@extends('layouts.app')

@section('content')
<x-layout.show-page 
    entity="spelling"
    title="Spelling Detail"
    :back-route="route('glossaries.spellings.index', $glossary)"
    :edit-route="route('glossaries.spellings.edit', [$glossary, $spelling])"
    :delete-route="route('glossaries.spellings.destroy', [$glossary, $spelling])"
    delete-confirm="Are you sure you want to delete this spelling?"
>
    @if(session('success'))
        <x-ui.alert :message="session('success')" type="success" entity="spelling" />
    @endif

    <x-display.description-list>
        <x-display.field label="Glossary Entry" :value="$glossary->internal_name" />
        <x-display.field label="Language" :value="$spelling->language->internal_name" />
        <x-display.field label="Spelling" :value="$spelling->spelling" />
    </x-display.description-list>
</x-layout.show-page>
@endsection
