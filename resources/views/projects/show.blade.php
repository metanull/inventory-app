@extends('layouts.app')

@section('content')
@php($c = $entityColor('projects'))
<div class="max-w-5xl mx-auto py-8 px-4 sm:px-6 lg:px-8 space-y-6">
    <x-entity.header entity="projects" :title="$project->internal_name">
        <div class="flex items-center gap-2">
            <a href="{{ route('projects.edit', $project) }}" class="inline-flex items-center px-3 py-1.5 rounded-md {{ $c['button'] }} text-sm font-medium">
                <x-heroicon-o-pencil-square class="w-5 h-5 mr-1" /> Edit
            </a>
            <form action="{{ route('projects.destroy', $project) }}" method="POST" onsubmit="return confirm('Delete this project?')">
                @csrf @method('DELETE')
                <button type="submit" class="inline-flex items-center px-3 py-1.5 rounded-md bg-red-600 hover:bg-red-700 text-white text-sm font-medium">
                    <x-heroicon-o-trash class="w-5 h-5 mr-1" /> Delete
                </button>
            </form>
        </div>
    </x-entity.header>

    <div class="bg-white shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg divide-y divide-gray-200">
        <dl>
            <div class="px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 bg-gray-50">
                <dt class="text-sm font-medium text-gray-700">Internal Name</dt>
                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $project->internal_name }}</dd>
            </div>
            <div class="px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                <dt class="text-sm font-medium text-gray-700">Launch Date</dt>
                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ optional($project->launch_date)->format('Y-m-d') ?? '—' }}</dd>
            </div>
            <div class="px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 bg-gray-50">
                <dt class="text-sm font-medium text-gray-700">Launched</dt>
                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $project->is_launched ? 'Yes' : 'No' }}</dd>
            </div>
            <div class="px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                <dt class="text-sm font-medium text-gray-700">Enabled</dt>
                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $project->is_enabled ? 'Yes' : 'No' }}</dd>
            </div>
            <div class="px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 bg-gray-50">
                <dt class="text-sm font-medium text-gray-700">Context</dt>
                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $project->context->internal_name ?? '—' }}</dd>
            </div>
            <div class="px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                <dt class="text-sm font-medium text-gray-700">Language</dt>
                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $project->language->internal_name ?? $project->language_id ?? '—' }}</dd>
            </div>
            <div class="px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 bg-gray-50">
                <dt class="text-sm font-medium text-gray-700">Legacy ID</dt>
                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $project->backward_compatibility ?? '—' }}</dd>
            </div>
        </dl>
    </div>
</div>
@endsection
