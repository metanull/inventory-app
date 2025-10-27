@extends('layouts.app')

@section('content')
    @php($c = $entityColor('users'))
    
    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <x-entity.header entity="users" :title="'Manage Roles: ' . $user->name">
            <x-slot name="action">
                <div class="flex gap-2">
                    <x-ui.button 
                        href="{{ route('admin.users.show', $user) }}" 
                        variant="secondary">
                        View User
                    </x-ui.button>
                    <x-ui.button 
                        href="{{ route('admin.users.edit', $user) }}" 
                        variant="secondary">
                        Edit User
                    </x-ui.button>
                    <x-ui.button 
                        href="{{ route('admin.users.index') }}" 
                        variant="secondary">
                        Back to Users
                    </x-ui.button>
                </div>
            </x-slot>
        </x-entity.header>

        <div class="space-y-6">
        <div class="space-y-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Current Roles -->
                <div class="bg-white overflow-hidden shadow sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Current Roles') }}</h3>
                        
                        @if($user->roles->count() > 0)
                            <div class="space-y-3">
                                @foreach($user->roles as $role)
                                    <div class="flex items-center justify-between p-3 {{ $c['lightBg'] }} rounded-lg border {{ $c['border'] }}">
                                        <div>
                                            <h4 class="font-medium {{ $c['darkText'] }}">{{ $role->name }}</h4>
                                            @if($role->permissions->count() > 0)
                                                <p class="text-sm {{ $c['mediumText'] }} mt-1">
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
                <div class="bg-white overflow-hidden shadow sm:rounded-lg">
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
                                                    class="{{ $c['accentLink'] }} text-sm font-medium"
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

            </div>

            <!-- Bulk Role Management -->
            <div class="bg-white overflow-hidden shadow sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Bulk Role Assignment') }}</h3>
                    
                    <form method="POST" action="{{ route('admin.users.updateRoles', $user) }}">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="action" value="sync">
                        
                        @php
                            $rolesCheckboxTextColor = 'text-' . $c['name'] . '-600';
                            $rolesCheckboxFocusBorder = 'focus:border-' . $c['name'] . '-300';
                            $rolesCheckboxFocusRing = 'focus:ring-' . $c['name'] . '-200';
                            $rolesCheckboxClass = 'mt-1 rounded border-gray-300 ' . $rolesCheckboxTextColor . ' shadow-sm ' . $rolesCheckboxFocusBorder . ' focus:ring ' . $rolesCheckboxFocusRing . ' focus:ring-opacity-50';
                        @endphp
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-4">
                            @foreach($roles as $role)
                                <label class="flex items-start">
                                    <input type="checkbox" 
                                           name="roles[]" 
                                           value="{{ $role->id }}"
                                           {{ $user->hasRole($role->name) ? 'checked' : '' }}
                                           class="{{ $rolesCheckboxClass }}">
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
                            <x-ui.button 
                                type="submit" 
                                variant="primary"
                                entity="users">
                                Update All Roles
                            </x-ui.button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection