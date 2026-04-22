@extends('layouts.app')

@section('content')
    @php
        $c = $entityColor('roles');
        $currentQuery = $listState->query();
        $formQuery = $listState->query(['q', 'page']);
        $sortQuery = $listState->query(['sort', 'direction', 'page']);
    @endphp

    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <x-entity.header entity="roles" title="Role Management">
            <x-ui.button
                href="{{ route('admin.roles.create') }}"
                variant="primary"
                entity="roles"
                icon="plus"
            >
                Create New Role
            </x-ui.button>
        </x-entity.header>

        @if (session('success'))
            <x-ui.alert type="success" :message="session('success')" entity="roles" />
        @endif

        @if (session('error'))
            <x-ui.alert type="error" :message="session('error')" entity="roles" />
        @endif

        <div class="space-y-6">
            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <x-list.search-form :action="route('admin.roles.index')" :query="$formQuery" :search="$listState->search" placeholder="Search by name or description..." :clear-url="route('admin.roles.index')" />
                </div>

            <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                <table class="min-w-full divide-y divide-gray-200">
                    <x-table.header>
                        <x-list.sort-link label="Name" field="name" :current-sort="$listState->sort" :current-direction="$listState->direction" :url="route('admin.roles.index')" :query="$sortQuery" />
                        <x-list.sort-link label="Description" field="description" :current-sort="$listState->sort" :current-direction="$listState->direction" :url="route('admin.roles.index')" :query="$sortQuery" />
                        <x-table.header-cell>Permissions</x-table.header-cell>
                        <x-table.header-cell>Users</x-table.header-cell>
                        <x-list.sort-link label="Created" field="created_at" :current-sort="$listState->sort" :current-direction="$listState->direction" :url="route('admin.roles.index')" :query="$sortQuery" class="hidden lg:table-cell" />
                        <x-table.header-cell><span class="sr-only">Actions</span></x-table.header-cell>
                    </x-table.header>

                    <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse($roles as $role)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900"><a href="{{ route('admin.roles.show', $role) }}" class="hover:text-indigo-700 hover:underline">{{ $role->name }}</a></td>
                                <td class="px-4 py-3 text-sm text-gray-500">{{ $role->description ?? __('No description') }}</td>
                                <td class="px-4 py-3 text-sm text-gray-500">{{ $role->permissions_count }} {{ __('permissions') }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">{{ $role->users_count }} {{ __('users') }}</td>
                                <td class="hidden lg:table-cell px-4 py-3 whitespace-nowrap text-xs text-gray-400">{{ optional($role->created_at)->format('Y-m-d H:i') }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-2">
                                        <a href="{{ route('admin.roles.show', $role) }}" class="{{ $c['accentLink'] }}">{{ __('View') }}</a>
                                        <a href="{{ route('admin.roles.permissions', $role) }}" class="{{ $c['accentLink'] }}">{{ __('Permissions') }}</a>
                                        <a href="{{ route('admin.roles.edit', $role) }}" class="{{ $c['accentLink'] }}">{{ __('Edit') }}</a>
                                        <x-ui.confirm-button :action="route('admin.roles.destroy', $role)" confirmMessage="Are you sure you want to delete this role?" variant="link-danger" size="sm" entity="roles">Delete</x-ui.confirm-button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-4 py-10 text-center text-sm text-gray-500">{{ __('No roles found.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <x-list.pagination :paginator="$roles" :action="route('admin.roles.index')" :query="$currentQuery" :current-per-page="$listState->perPage" entity="roles" />
            </div>
        </div>
    </div>
@endsection
