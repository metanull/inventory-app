<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Manage Permissions') }}: {{ $role->name }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('admin.roles.show', $role) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    {{ __('View Role') }}
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
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Current Permissions -->
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Current Permissions') }}</h3>
                        
                        @if($role->permissions->count() > 0)
                            <div class="space-y-3">
                                @foreach($role->permissions as $permission)
                                    <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg border border-green-200">
                                        <div>
                                            <h4 class="font-medium text-green-900">{{ $permission->name }}</h4>
                                            @if($permission->description)
                                                <p class="text-sm text-green-700 mt-1">{{ $permission->description }}</p>
                                            @endif
                                        </div>
                                        <form method="POST" action="{{ route('admin.roles.updatePermissions', $role) }}" class="inline">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="action" value="remove">
                                            <input type="hidden" name="permission_id" value="{{ $permission->id }}">
                                            <button type="submit" 
                                                    class="text-red-600 hover:text-red-800 text-sm font-medium"
                                                    onclick="return confirm('{{ __('Are you sure you want to remove this permission?') }}')">
                                                {{ __('Remove') }}
                                            </button>
                                        </form>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-gray-500 italic">{{ __('This role has no permissions assigned.') }}</p>
                        @endif
                    </div>
                </div>

                <!-- Available Permissions -->
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Available Permissions') }}</h3>
                        
                        @php
                            $availablePermissions = $permissions->whereNotIn('id', $role->permissions->pluck('id'));
                        @endphp
                        
                        @if($availablePermissions->count() > 0)
                            <div class="space-y-3">
                                @foreach($availablePermissions as $permission)
                                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-200">
                                        <div>
                                            <h4 class="font-medium text-gray-900">{{ $permission->name }}</h4>
                                            @if($permission->description)
                                                <p class="text-sm text-gray-600 mt-1">{{ $permission->description }}</p>
                                            @endif
                                        </div>
                                        <form method="POST" action="{{ route('admin.roles.updatePermissions', $role) }}" class="inline">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="action" value="add">
                                            <input type="hidden" name="permission_id" value="{{ $permission->id }}">
                                            <button type="submit" class="text-green-600 hover:text-green-800 text-sm font-medium">
                                                {{ __('Add') }}
                                            </button>
                                        </form>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-gray-500 italic">{{ __('All permissions have been assigned to this role.') }}</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Bulk Permission Management -->
            <div class="mt-6 bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Bulk Permission Management') }}</h3>
                    
                    <form method="POST" action="{{ route('admin.roles.updatePermissions', $role) }}">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="action" value="sync">
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-4">
                            @foreach($permissions as $permission)
                                <label class="flex items-start">
                                    <input type="checkbox" 
                                           name="permissions[]" 
                                           value="{{ $permission->id }}"
                                           {{ $role->hasPermissionTo($permission->name) ? 'checked' : '' }}
                                           class="mt-1 rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    <div class="ml-3">
                                        <span class="text-sm font-medium text-gray-700">{{ $permission->name }}</span>
                                        @if($permission->description)
                                            <p class="text-xs text-gray-500 mt-1">{{ $permission->description }}</p>
                                        @endif
                                    </div>
                                </label>
                            @endforeach
                        </div>
                        
                        <div class="flex justify-end">
                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                {{ __('Synchronize Permissions') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
