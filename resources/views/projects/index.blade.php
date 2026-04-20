@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <x-entity.header entity="projects" title="Projects">
        @can(\App\Enums\Permission::CREATE_DATA->value)
            <x-ui.button :href="route('projects.create')" variant="primary" entity="projects" icon="plus">
                Add Project
            </x-ui.button>
        @endcan
    </x-entity.header>

    @if(session('status'))
        <x-ui.alert :message="session('status')" type="success" entity="projects" />
    @endif

    <div class="space-y-4">
        <x-table.filter-bar name="q" placeholder="Search internal name..." />

        <div class="bg-white shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <x-table.header>
                    <x-table.sortable-header field="internal_name" label="Internal Name" />
                    <x-table.sortable-header field="launch_date" label="Launch Date" class="hidden md:table-cell" />
                    <x-table.sortable-header field="is_launched" label="Launched" class="hidden sm:table-cell" />
                    <x-table.sortable-header field="is_enabled" label="Enabled" class="hidden sm:table-cell" />
                    <x-table.sortable-header field="created_at" label="Created" class="hidden lg:table-cell" />
                    <x-table.header-cell hidden="hidden sm:table-cell">
                        <span class="sr-only">Actions</span>
                    </x-table.header-cell>
                </x-table.header>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($projects as $project)
                        <tr class="hover:bg-gray-50 cursor-pointer" onclick="window.location='{{ route('projects.show', $project) }}'">
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $project->internal_name }}</td>
                            <td class="hidden md:table-cell px-4 py-3 text-sm text-gray-500">{{ optional($project->launch_date)->format('Y-m-d') ?? '—' }}</td>
                            <td class="hidden sm:table-cell px-4 py-3 text-sm">
                                <x-ui.badge :color="$project->is_launched ? 'green' : 'gray'" variant="pill">
                                    {{ $project->is_launched ? 'Yes' : 'No' }}
                                </x-ui.badge>
                            </td>
                            <td class="hidden sm:table-cell px-4 py-3 text-sm">
                                <x-ui.badge :color="$project->is_enabled ? 'green' : 'gray'" variant="pill">
                                    {{ $project->is_enabled ? 'Yes' : 'No' }}
                                </x-ui.badge>
                            </td>
                            <td class="hidden lg:table-cell px-4 py-3 text-xs text-gray-400">{{ optional($project->created_at)->format('Y-m-d H:i') }}</td>
                            <td class="hidden sm:table-cell px-4 py-3 text-right text-sm" onclick="event.stopPropagation()">
                                <x-table.row-actions
                                    :view="route('projects.show', $project)"
                                    :edit="route('projects.edit', $project)"
                                    :delete="route('projects.destroy', $project)"
                                    delete-confirm="Delete this project?"
                                    entity="projects"
                                    :record-id="$project->id"
                                    :record-name="$project->internal_name"
                                />
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500">No projects found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div>
            <x-layout.pagination
                :paginator="$projects"
                entity="projects"
                param-page="page"
            />
        </div>

        <x-table.delete-modal />
    </div>
</div>
@endsection
