@extends('layouts.app')

@section('content')
    <x-layout.index-page 
        entity="collection_translations" 
        title="Collection Translations"
        :create-route="route('collection-translations.create')"
        create-button-text="Add Translation"
    >
        <livewire:dynamic-component :is="'tables.collection-translations-table'" />
    </x-layout.index-page>
@endsection
