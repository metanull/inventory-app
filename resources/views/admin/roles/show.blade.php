<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Role Details') }}: {{ $role->name }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('admin.roles.permissions', $role) }}" class="bg-purple-500 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded">
                    {{ __('Manage Permissions') }}
                </a>
                <a href="{{ route('admin.roles.edit', $role) }}" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                    {{ __('Edit Role') }}
                </a>
                <a href="{{ route('admin.roles.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    {{ __('Back to Roles') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Role Information -->
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Role Information') }}</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-500">{{ __('Name') }}</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $role->name }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500">{{ __('Guard Name') }}</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $role->guard_name }}</p>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-500">{{ __('Description') }}</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $role->description ?? __('No description provided') }}</p>
                        </div>

                    </div>
                </div>
            </div>

            <!-- Assigned Permissions -->
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">
                        {{ __('Assigned Permissions') }} ({{ $role->permissions->count() }})
                    </h3>
                    
                    @if($role->permissions->count() > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($role->permissions as $permission)
                                <div class="p-3 bg-green-50 rounded-lg border border-green-200">
                                    <h4 class="font-medium text-green-900">{{ $permission->name }}</h4>
                                    @if($permission->description)
                                        <p class="text-sm text-green-700 mt-1">{{ $permission->description }}</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500 italic">{{ __('This role has no permissions assigned.') }}</p>
                    @endif
                </div>
            </div>

            <!-- Users with this Role -->
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">
                        {{ __('Users with this Role') }} ({{ $role->users->count() }})
                    </h3>
                    
                    @if($role->users->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ __('Name') }}
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ __('Email') }}
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ __('Actions') }}
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
                                                    <a href="{{ route('admin.users.show', $user) }}" class="text-blue-600 hover:text-blue-900">
                                                        {{ __('View User') }}
                                                    </a>
                                                @endcan
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-gray-500 italic">{{ __('No users have been assigned this role.') }}</p>
                    @endif
                </div>
            </div>

            <!-- Danger Zone -->
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg border-2 border-red-200">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-red-900 mb-4">{{ __('Danger Zone') }}</h3>
                    
                    <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                        <p class="text-sm text-red-700 mb-4">
                            {{ __('Deleting this role will remove it from all users. This action cannot be undone.') }}
                            @if($role->users->count() > 0)
                                <br>
                                <strong>{{ __('Warning: This role is currently assigned to :count user(s).', ['count' => $role->users->count()]) }}</strong>
                            @endif
                        </p>
                        <form method="POST" action="{{ route('admin.roles.destroy', $role) }}" 
                              onsubmit="return confirm('{{ __('Are you sure you want to delete this role? This action cannot be undone.') }}');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                                {{ __('Delete Role') }}
                            </button>
                        </form>
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
    </div>
</x-app-layout>
