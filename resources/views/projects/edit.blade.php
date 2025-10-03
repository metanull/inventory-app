@extends('layouts.app')

@section('content')
<x-layout.form-page 
    entity="projects" 
    title="Edit Project" 
    :back-route="route('projects.show', $project)"
    :submit-route="route('projects.update', $project)" 
    method="PUT">
    @include('projects._form')
</x-layout.form-page>
@endsection
