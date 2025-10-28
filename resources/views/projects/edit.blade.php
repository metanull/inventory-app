@extends('layouts.app')

@section('content')
    <x-layout.edit-page entity="projects" :model="$project">
        @include('projects._form')
    </x-layout.edit-page>
@endsection
