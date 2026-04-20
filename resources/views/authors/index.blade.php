@extends('layouts.app')

@section('content')
    <x-layout.index-page entity="authors">
        <livewire:dynamic-component :is="'tables.authors-table'" />
    </x-layout.index-page>
@endsection
