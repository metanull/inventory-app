@extends('layouts.app')

@section('content')
    <x-layout.index-page 
        entity="item-item-links" 
        title="Links for {{ $item->internal_name }}"
        createRoute="{{ route('item-links.create', $item) }}"
        createButtonText="Add Link"
    >
        <livewire:dynamic-component :is="'tables.item-item-links-table'" />
    </x-layout.index-page>
@endsection

