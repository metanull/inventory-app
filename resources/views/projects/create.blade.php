@extends('layouts.app')

@section('content')
<x-layout.form-page 
    entity="projects" 
    title="Create Project" 
    :back-route="route('projects.index')"
    :submit-route="route('projects.store')">
    @include('projects._form', ['project' => null])
</x-layout.form-page>
@endsection
