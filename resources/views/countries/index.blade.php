@extends('layouts.app')

@section('content')
    <x-layout.index-page entity="countries">
        <livewire:dynamic-component :is="'tables.countries-table'" />
    </x-layout.index-page>
@endsection
