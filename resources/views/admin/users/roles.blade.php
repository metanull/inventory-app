<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Manage Roles') }}: {{ $user->name }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('admin.users.show', $user) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    {{ __('View User') }}
                </a>
                <a href="{{ route('admin.users.edit', $user) }}" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                    {{ __('Edit User') }}
                </a>
                <a href="{{ route('admin.users.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    {{ __('Back to Users') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Current Roles -->
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Current Roles') }}</h3>
                        
                        @if($user->roles->count() > 0)
                            <div class="space-y-3">
                                @foreach($user->roles as $role)
                                    <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg border border-green-200">
                                        <div>
                                            <h4 class="font-medium text-green-900">{{ $role->name }}</h4>
                                            @if($role->permissions->count() > 0)
                                                <p class="text-sm text-green-700 mt-1">
                                                    {{ __('Permissions') }}: {{ $role->permissions->pluck('name')->implode(', ') }}
                                                </p>
                                            @endif
                                        </div>
                                        <form method="POST" action="{{ route('admin.users.updateRoles', $user) }}" class="inline">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="action" value="remove">
                                            <input type="hidden" name="role_id" value="{{ $role->id }}">
                                            <button type="submit" 
                                                    class="text-red-600 hover:text-red-800 text-sm font-medium"
                                                    onclick="return confirm('{{ __('Are you sure you want to remove this role?') }}')">
                                                {{ __('Remove') }}
                                            </button>
                                        </form>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-gray-500 italic">{{ __('This user has no roles assigned.') }}</p>
                        @endif
                    </div>
                </div>

                <!-- Available Roles -->
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Available Roles') }}</h3>
                        
                        @php
                            $availableRoles = $roles->whereNotIn('id', $user->roles->pluck('id'));
                        @endphp
                        
                        @if($availableRoles->count() > 0)
                            <div class="space-y-3">
                                @foreach($availableRoles as $role)
                                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-200">
                                        <div>
                                            <h4 class="font-medium text-gray-900">{{ $role->name }}</h4>
                                            @if($role->permissions->count() > 0)
                                                <p class="text-sm text-gray-600 mt-1">
                                                    {{ __('Permissions') }}: {{ $role->permissions->pluck('name')->implode(', ') }}
                                                </p>
                                            @endif
                                        </div>
                                        <form method="POST" action="{{ route('admin.users.updateRoles', $user) }}" class="inline">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="action" value="add">
                                            <input type="hidden" name="role_id" value="{{ $role->id }}">
                                            <button type="submit" 
                                                    class="text-green-600 hover:text-green-800 text-sm font-medium"
                                                    onclick="return confirm('{{ __('Are you sure you want to assign this role?') }}')">
                                                {{ __('Assign') }}
                                            </button>
                                        </form>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-gray-500 italic">{{ __('All available roles have been assigned to this user.') }}</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Bulk Role Management -->
            <div class="mt-6 bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Bulk Role Assignment') }}</h3>
                    
                    <form method="POST" action="{{ route('admin.users.updateRoles', $user) }}">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="action" value="sync">
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-4">
                            @foreach($roles as $role)
                                <label class="flex items-start">
                                    <input type="checkbox" 
                                           name="roles[]" 
                                           value="{{ $role->id }}"
                                           {{ $user->hasRole($role->name) ? 'checked' : '' }}
                                           class="mt-1 rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    <div class="ml-3">
                                        <span class="text-sm font-medium text-gray-700">{{ $role->name }}</span>
                                        @if($role->permissions->count() > 0)
                                            <p class="text-xs text-gray-500 mt-1">
                                                {{ $role->permissions->pluck('name')->implode(', ') }}
                                            </p>
                                        @endif
                                    </div>
                                </label>
                            @endforeach
                        </div>
                        
                        <div class="flex items-center justify-end">
                            <button type="submit" class="bg-indigo-500 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">
                                {{ __('Update All Roles') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>