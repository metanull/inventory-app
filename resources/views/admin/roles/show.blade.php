@extends('layouts.app')

@section('content')
    <div class="max-w-3xl mx-auto py-8 px-4 sm:px-6 lg:px-8 space-y-8">
        @php($c = $entityColor('roles'))
        
        <div>
            <a href="{{ route('admin.roles.index') }}" class="text-sm {{ $c['accentLink'] }}">&larr; Back to Roles</a>
        </div>
        
        <x-entity.header entity="roles" :title="$role->name">
            <div class="flex gap-2">
                <x-ui.button 
                    href="{{ route('admin.roles.permissions', $role) }}" 
                    variant="primary"
                    entity="roles"
                    icon="shield-check">
                    Manage Permissions
                </x-ui.button>
                <x-ui.button 
                    href="{{ route('admin.roles.edit', $role) }}" 
                    variant="secondary"
                    entity="roles"
                    icon="pencil">
                    Edit
                </x-ui.button>
            </div>
        </x-entity.header>

        <!-- Role Information -->
        <div class="bg-white shadow sm:rounded-lg">
            <div class="px-6 py-5">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Role Information</h3>
                
                <x-display.description-list>
                    <x-display.field label="Name" :value="$role->name" />
                    <x-display.field label="Guard Name" :value="$role->guard_name" />
                    <x-display.field label="Description" :value="$role->description ?? 'No description provided'" full-width />
                </x-display.description-list>
            </div>
        </div>

        <!-- Assigned Permissions -->
        <div class="bg-white shadow sm:rounded-lg">
            <div class="px-6 py-5">
                <h3 class="text-lg font-medium text-gray-900 mb-4">
                    Assigned Permissions ({{ $role->permissions->count() }})
                </h3>
                
                @if($role->permissions->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($role->permissions as $permission)
                            <div class="p-3 {{ $c['bg'] }} rounded-lg border {{ $c['border'] }}">
                                <h4 class="font-medium {{ $c['text'] }}">{{ $permission->name }}</h4>
                                @if($permission->description)
                                    <p class="text-sm text-gray-700 mt-1">{{ $permission->description }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 italic">This role has no permissions assigned.</p>
                @endif
            </div>
        </div>

        <!-- Users with this Role -->
        <div class="bg-white shadow sm:rounded-lg">
            <div class="px-6 py-5">
                <h3 class="text-lg font-medium text-gray-900 mb-4">
                    Users with this Role ({{ $role->users->count() }})
                </h3>
                
                @if($role->users->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Name
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Email
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($role->users as $user)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-500">{{ $user->email }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            @can('manage users')
                                                <a href="{{ route('admin.users.show', $user) }}" class="{{ $c['accentLink'] }}">
                                                    View User
                                                </a>
                                            @endcan
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-gray-500 italic">No users have been assigned this role.</p>
                @endif
            </div>
        </div>

        <!-- Danger Zone -->
        <div class="bg-white shadow sm:rounded-lg border-2 border-red-200">
            <div class="px-6 py-5">
                <h3 class="text-lg font-medium text-red-900 mb-4">Danger Zone</h3>
                
                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                    <p class="text-sm text-red-700 mb-4">
                        Deleting this role will remove it from all users. This action cannot be undone.
                        @if($role->users->count() > 0)
                            <br>
                            <strong>Warning: This role is currently assigned to {{ $role->users->count() }} user(s).</strong>
                        @endif
                    </p>
                    <x-ui.confirm-button 
                        :action="route('admin.roles.destroy', $role)"
                        confirmMessage="Are you sure you want to delete this role? This action cannot be undone."
                        variant="danger"
                        entity="roles">
                        Delete Role
                    </x-ui.confirm-button>
                </div>
            </div>
        </div>

        <!-- System Properties -->
        <x-system-properties 
            :id="$role->id"
            :created-at="$role->created_at"
            :updated-at="$role->updated_at"
        />
    </div>
@endsection
