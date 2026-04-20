@extends('layouts.app')

@section('content')
    <x-layout.index-page 
        entity="partner-translations" 
        title="Partner Translations"
        createButtonText="Add Translation"
    >
        <livewire:dynamic-component :is="'tables.partner-translations-table'" />
    </x-layout.index-page>
@endsection
