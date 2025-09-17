@extends('layouts.app')

@section('content')
@php($c = $entityColor('projects'))
<div class="max-w-3xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <x-entity.header entity="projects" title="Create Project" />

    <form method="POST" action="{{ route('projects.store') }}" class="bg-white shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg divide-y divide-gray-200">
        @include('projects._form')
    </form>
</div>
@endsection
