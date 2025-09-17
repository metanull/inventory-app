@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        @php($c = $entityColor('projects'))
        <x-entity.header entity="projects" title="Projects">
            <a href="{{ route('projects.create') }}" class="inline-flex items-center px-3 py-2 rounded-md {{ $c['button'] }} text-sm font-medium">
                <x-heroicon-o-plus class="w-5 h-5 mr-1" />
                Add Project
            </a>
        </x-entity.header>

        <livewire:tables.projects-table />
    </div>
@endsection
