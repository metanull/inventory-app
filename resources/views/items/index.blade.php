@extends('layouts.app')

@section('content')
    <x-layout.index-page entity="items">
        <livewire:dynamic-component :is="'tables.items-table'" />
    </x-layout.index-page>
@endsection
