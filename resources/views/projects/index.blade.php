@extends('layouts.app')

@section('content')
    <x-layout.index-page entity="projects">
        <livewire:dynamic-component :is="'tables.projects-table'" />
    </x-layout.index-page>
@endsection
