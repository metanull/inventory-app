@extends('layouts.app')

@section('content')
    <x-layout.index-page 
        entity="glossary" 
        createRoute="{{ route('glossaries.create') }}" 
        createButtonText="Add Entry" 
    >
        <livewire:dynamic-component :is="'tables.glossary-table'" />
    </x-layout.index-page>
@endsection
