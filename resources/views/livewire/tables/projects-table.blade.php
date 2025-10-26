<div class="space-y-4">
    <x-table.filter-bar wireModel="q" placeholder="Search internal name..." />

    <div class="bg-white shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <x-table.sortable-header field="internal_name" label="Internal Name" :sort-by="$sortBy" :sort-direction="$sortDirection" />
                    <x-table.sortable-header field="launch_date" label="Launch Date" :sort-by="$sortBy" :sort-direction="$sortDirection" class="hidden md:table-cell" />
                    <x-table.sortable-header field="is_launched" label="Launched" :sort-by="$sortBy" :sort-direction="$sortDirection" class="hidden sm:table-cell" />
                    <x-table.sortable-header field="is_enabled" label="Enabled" :sort-by="$sortBy" :sort-direction="$sortDirection" class="hidden sm:table-cell" />
                    <x-table.sortable-header field="created_at" label="Created" :sort-by="$sortBy" :sort-direction="$sortDirection" class="hidden lg:table-cell" />
                    <th class="hidden sm:table-cell px-4 py-3">
                        <span class="sr-only">Actions</span>
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($projects as $project)
                    <tr class="hover:bg-gray-50 cursor-pointer" wire:key="project-{{ $project->id }}" onclick="window.location='{{ route('projects.show', $project) }}'">
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $project->internal_name }}</td>
                        <td class="hidden md:table-cell px-4 py-3 text-sm text-gray-500">{{ optional($project->launch_date)->format('Y-m-d') ?? '—' }}</td>
                        <td class="hidden sm:table-cell px-4 py-3 text-sm">
                            <span class="inline-flex px-2 py-0.5 rounded text-xs {{ $project->is_launched ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-600' }}">{{ $project->is_launched ? 'Yes' : 'No' }}</span>
                        </td>
                        <td class="hidden sm:table-cell px-4 py-3 text-sm">
                            <span class="inline-flex px-2 py-0.5 rounded text-xs {{ $project->is_enabled ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-600' }}">{{ $project->is_enabled ? 'Yes' : 'No' }}</span>
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
    
    <!-- Delete confirmation modal -->
    <x-table.delete-modal />
</div>
