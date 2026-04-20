@extends('layouts.app')

@section('content')
    <x-layout.index-page entity="partners">
        <livewire:dynamic-component :is="'tables.partners-table'" />
    </x-layout.index-page>
@endsection
