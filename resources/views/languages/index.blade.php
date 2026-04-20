@extends('layouts.app')

@section('content')
    <x-layout.index-page entity="languages">
        <livewire:dynamic-component :is="'tables.languages-table'" />
    </x-layout.index-page>
@endsection
