@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        @php($c = $entityColor('roles'))

        <x-entity.header entity="roles" title="Role Management">
            <x-ui.button 
                href="{{ route('admin.roles.create') }}" 
                variant="primary"
                entity="roles"
                icon="plus">
                Create New Role
            </x-ui.button>
        </x-entity.header>

        @if (session('success'))
            <x-ui.alert type="success" :message="session('success')" entity="roles" />
        @endif

        @if (session('error'))
            <x-ui.alert type="error" :message="session('error')" entity="roles" />
        @endif

        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
            <div class="p-6">
                <!-- Search Form -->
                <form method="GET" action="{{ route('admin.roles.index') }}" class="mb-6">
                    <div class="flex gap-4">
                        <div class="flex-1">
                            <input type="text" name="search" value="{{ request('search') }}" 
                                   placeholder="{{ __('Search by name or description...') }}"
                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        </div>
                        <x-ui.button type="submit" variant="primary" entity="roles">
                            Search
                        </x-ui.button>
                        @if(request('search'))
                            <x-ui.button href="{{ route('admin.roles.index') }}" variant="secondary" entity="roles">
                                Clear
                            </x-ui.button>
                        @endif
                    </div>
                </form>

                    <!-- Roles Table -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <x-table.header>
                                <x-table.header-cell class="px-6">{{ __('Name') }}</x-table.header-cell>
                                <x-table.header-cell class="px-6">{{ __('Description') }}</x-table.header-cell>
                                <x-table.header-cell class="px-6">{{ __('Permissions') }}</x-table.header-cell>
                                <x-table.header-cell class="px-6">{{ __('Users') }}</x-table.header-cell>
                                <x-table.header-cell class="px-6">{{ __('Actions') }}</x-table.header-cell>
                            </x-table.header>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($roles as $role)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ $role->name }}</div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm text-gray-500">{{ $role->description ?? __('No description') }}</div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm text-gray-500">
                                                {{ $role->permissions->count() }} {{ __('permissions') }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-500">{{ $role->users->count() }} {{ __('users') }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex space-x-2">
                                                <a href="{{ route('admin.roles.show', $role) }}" class="{{ $c['accentLink'] }}">{{ __('View') }}</a>
                                                <a href="{{ route('admin.roles.permissions', $role) }}" class="{{ $c['accentLink'] }}">{{ __('Permissions') }}</a>
                                                <a href="{{ route('admin.roles.edit', $role) }}" class="{{ $c['accentLink'] }}">{{ __('Edit') }}</a>
                                                <x-ui.confirm-button 
                                                    :action="route('admin.roles.destroy', $role)"
                                                    confirmMessage="Are you sure you want to delete this role?"
                                                    variant="link-danger"
                                                    size="sm"
                                                    entity="roles">
                                                    Delete
                                                </x-ui.confirm-button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">
                                            {{ __('No roles found.') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-4">
                        {{ $roles->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
