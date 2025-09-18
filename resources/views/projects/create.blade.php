@extends('layouts.app')

@section('content')
<x-layout.form-page entity="projects" title="Create Project" :action="route('projects.store')">
    @include('projects._form')
</x-layout.form-page>
@endsection
