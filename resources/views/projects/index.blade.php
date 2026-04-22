@extends('layouts.app')

@section('content')
    <x-layout.index-page entity="projects">
        @php
            $currentQuery = $listState->query();
            $formQuery = $listState->query(['q', 'page']);
            $sortQuery = $listState->query(['sort', 'direction', 'page']);
        @endphp

        <div class="space-y-6">
            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <x-list.search-form :action="route('projects.index')" :query="$formQuery" :search="$listState->search" placeholder="Search internal names..." :clear-url="route('projects.index')" />
            </div>

            <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                <table class="min-w-full divide-y divide-gray-200">
                    <x-table.header>
                        <x-list.sort-link label="Internal Name" field="internal_name" :current-sort="$listState->sort" :current-direction="$listState->direction" :url="route('projects.index')" :query="$sortQuery" />
                        <x-list.sort-link label="Launch Date" field="launch_date" :current-sort="$listState->sort" :current-direction="$listState->direction" :url="route('projects.index')" :query="$sortQuery" class="hidden md:table-cell" />
                        <x-list.sort-link label="Launched" field="is_launched" :current-sort="$listState->sort" :current-direction="$listState->direction" :url="route('projects.index')" :query="$sortQuery" class="hidden sm:table-cell" />
                        <x-list.sort-link label="Enabled" field="is_enabled" :current-sort="$listState->sort" :current-direction="$listState->direction" :url="route('projects.index')" :query="$sortQuery" class="hidden sm:table-cell" />
                        <x-list.sort-link label="Created" field="created_at" :current-sort="$listState->sort" :current-direction="$listState->direction" :url="route('projects.index')" :query="$sortQuery" class="hidden lg:table-cell" />
                        <x-table.header-cell hidden="hidden sm:table-cell"><span class="sr-only">Actions</span></x-table.header-cell>
                    </x-table.header>

                    <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse($projects as $project)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm font-medium text-gray-900"><a href="{{ route('projects.show', $project) }}" class="text-teal-700 hover:text-teal-900 hover:underline">{{ $project->internal_name }}</a></td>
                                <td class="hidden md:table-cell px-4 py-3 text-sm text-gray-500">{{ optional($project->launch_date)->format('Y-m-d') ?? '—' }}</td>
                                <td class="hidden sm:table-cell px-4 py-3 text-sm"><x-ui.badge :color="$project->is_launched ? 'green' : 'gray'" variant="pill">{{ $project->is_launched ? 'Yes' : 'No' }}</x-ui.badge></td>
                                <td class="hidden sm:table-cell px-4 py-3 text-sm"><x-ui.badge :color="$project->is_enabled ? 'green' : 'gray'" variant="pill">{{ $project->is_enabled ? 'Yes' : 'No' }}</x-ui.badge></td>
                                <td class="hidden lg:table-cell px-4 py-3 text-xs text-gray-400">{{ optional($project->created_at)->format('Y-m-d H:i') }}</td>
                                <td class="hidden sm:table-cell px-4 py-3 text-right text-sm"><x-table.row-actions :view="route('projects.show', $project)" :edit="route('projects.edit', $project)" :delete="route('projects.destroy', $project)" delete-confirm="Delete this project?" entity="projects" :record-id="$project->id" :record-name="$project->internal_name" /></td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-4 py-10 text-center text-sm text-gray-500">No projects found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <x-list.pagination :paginator="$projects" :action="route('projects.index')" :query="$currentQuery" :current-per-page="$listState->perPage" entity="projects" />
        </div>
    </x-layout.index-page>
@endsection
