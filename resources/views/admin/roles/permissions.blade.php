@extends('layouts.app')

@section('content')
    @php
        $c = $entityColor('roles');
        $pageTitle = 'Manage Permissions: ' . $role->name;
    @endphp
    
    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8 space-y-6">
        <div>
            <a href="{{ route('admin.roles.index') }}" class="text-sm {{ $c['accentLink'] }}">&larr; Back to Roles</a>
        </div>
        
        <x-entity.header entity="roles" :title="$pageTitle">
            <div class="flex gap-2">
                <x-ui.button 
                    href="{{ route('admin.roles.show', $role) }}" 
                    variant="secondary"
                    entity="roles"
                    icon="eye">
                    View Role
                </x-ui.button>
                <x-ui.button 
                    href="{{ route('admin.roles.edit', $role) }}" 
                    variant="secondary"
                    entity="roles"
                    icon="pencil">
                    Edit Role
                </x-ui.button>
            </div>
        </x-entity.header>

        @if (session('success'))
            <x-ui.alert type="success" :message="session('success')" entity="roles" />
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Current Permissions -->
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Current Permissions</h3>
                    
                    @if($role->permissions->count() > 0)
                        <div class="space-y-3">
                            @foreach($role->permissions as $permission)
                                <div class="flex items-center justify-between p-3 {{ $c['bg'] }} rounded-lg border {{ $c['border'] }}">
                                    <div>
                                        <h4 class="font-medium {{ $c['text'] }}">{{ $permission->name }}</h4>
                                        @if($permission->description)
                                            <p class="text-sm text-gray-700 mt-1">{{ $permission->description }}</p>
                                        @endif
                                    </div>
                                    <form method="POST" action="{{ route('admin.roles.updatePermissions', $role) }}" class="inline">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="action" value="remove">
                                        <input type="hidden" name="permission_id" value="{{ $permission->id }}">
                                        <x-ui.confirm-button 
                                            confirmMessage="Are you sure you want to remove this permission?"
                                            variant="link-danger"
                                            size="sm"
                                            entity="roles">
                                            Remove
                                        </x-ui.confirm-button>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500 italic">This role has no permissions assigned.</p>
                    @endif
                </div>
            </div>

            <!-- Available Permissions -->
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Available Permissions</h3>
                    
                    @php
                        $available = $permissions->whereNotIn('id', $role->permissions->pluck('id'));
                    @endphp
                    
                    @if($available->count() > 0)
                        <div class="space-y-3">
                            @foreach($available as $permission)
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
                                        <x-ui.button type="submit" variant="link-success" size="sm" entity="roles">
                                            Add
                                        </x-ui.button>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500 italic">All permissions have been assigned to this role.</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Bulk Permission Management -->
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Bulk Permission Management</h3>
                
                <form method="POST" action="{{ route('admin.roles.updatePermissions', $role) }}">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="action" value="sync">
                    
                    @php
                        $selectedPermissions = $role->permissions->pluck('id')->toArray();
                    @endphp
                    
                    <x-form.checkbox-list 
                        :items="$permissions"
                        name="permissions[]"
                        :selected="$selectedPermissions"
                        entity="roles"
                        class="mb-4"
                    />
                    
                    <div class="flex justify-end">
                        <x-ui.button type="submit" variant="primary" entity="roles">
                            Synchronize Permissions
                        </x-ui.button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
