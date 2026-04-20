@extends('layouts.app')

@section('content')
    <x-layout.index-page entity="tags">
        <livewire:dynamic-component :is="'tables.tags-table'" />
    </x-layout.index-page>
@endsection
