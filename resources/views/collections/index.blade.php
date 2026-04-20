@extends('layouts.app')

@section('content')
    <x-layout.index-page entity="collections">
        <livewire:dynamic-component :is="'tables.collections-table'" />
    </x-layout.index-page>
@endsection
