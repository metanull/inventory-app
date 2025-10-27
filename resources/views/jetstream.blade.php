@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <x-entity.header entity="users" title="Jetstream" />

        <div class="bg-white overflow-hidden shadow sm:rounded-lg">
            <x-welcome />
        </div>
    </div>
@endsection
