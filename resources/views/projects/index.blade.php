@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <x-entity.header entity="projects" title="Projects">
            @can(\App\Enums\Permission::CREATE_DATA->value)
                <x-ui.button 
                    href="{{ route('projects.create') }}" 
                    variant="primary" 
                    entity="projects"
                    icon="plus">
                    Add Project
                </x-ui.button>
            @endcan
        </x-entity.header>

        @if(session('status'))
            <x-ui.alert :message="session('status')" type="success" entity="projects" />
        @endif

        <livewire:tables.projects-table />
    </div>
@endsection
