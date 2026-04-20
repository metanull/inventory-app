@extends('layouts.app')

@section('content')
    <x-layout.index-page entity="contexts">
        <livewire:dynamic-component :is="'tables.contexts-table'" />
    </x-layout.index-page>
@endsection
