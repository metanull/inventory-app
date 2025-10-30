@extends('layouts.app')

@section('content')
    <x-layout.index-page 
        entity="item-item-links" 
        title="Links for {{ $item->internal_name }}"
        createRoute="{{ route('item-links.create', $item) }}"
        createButtonText="Add Link"
        livewireTable="tables.item-item-links-table"
    />
@endsection

