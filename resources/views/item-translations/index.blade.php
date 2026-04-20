@extends('layouts.app')

@section('content')
    <x-layout.index-page 
        entity="item-translations" 
        title="Item Translations"
        createButtonText="Add Translation"
    >
        <livewire:dynamic-component :is="'tables.item-translations-table'" />
    </x-layout.index-page>
@endsection
