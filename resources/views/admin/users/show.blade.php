@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8 space-y-6">
        @php($c = $entityColor('users'))
        
        <div>
            <a href="{{ route('admin.users.index') }}" class="text-sm {{ $c['accentLink'] }}">&larr; Back to Users</a>
        </div>
        
        <x-entity.header entity="users" :title="$user->name">
            @can(\App\Enums\Permission::MANAGE_USERS->value)
                <x-ui.button 
                    href="{{ route('admin.users.edit', $user) }}" 
                    variant="secondary"
                    entity="users"
                    icon="pencil">
                    Edit User
                </x-ui.button>
            @endcan
        </x-entity.header>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- User Information -->
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">User Information</h3>
                    
                    <x-display.description-list>
                        <x-display.field label="Name" :value="$user->name" />
                        <x-display.field label="Email" :value="$user->email" />
                        <x-display.field label="Email Verified">
                            @if($user->email_verified_at)
                                <x-ui.badge color="green" variant="pill">
                                    Verified ({{ $user->email_verified_at->format('M j, Y H:i') }})
                                </x-ui.badge>
                            @else
                                <x-ui.badge color="yellow" variant="pill">
                                    Not Verified
                                </x-ui.badge>
                            @endif
                        </x-display.field>
                    </x-display.description-list>
                </div>
            </div>

            <!-- Roles and Permissions -->
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Roles & Permissions</h3>
                        @can(\App\Enums\Permission::ASSIGN_ROLES->value)
                            <a href="{{ route('admin.users.edit', $user) }}" class="{{ $c['accentLink'] }}">
                                Edit User & Roles
                            </a>
                        @endcan
                    </div>
                    
                    <!-- Roles -->
                    <div class="mb-6">
                        <h4 class="text-sm font-medium text-gray-700 mb-2">Assigned Roles</h4>
                        @if($user->roles->count() > 0)
                            <div class="space-y-2">
                                @foreach($user->roles as $role)
                                    <div class="flex items-center justify-between p-3 {{ $c['bg'] }} rounded-lg">
                                        <div>
                                            <div class="font-medium {{ $c['text'] }}">{{ $role->name }}</div>
                                            @if($role->description)
                                                <div class="text-sm text-gray-700">{{ $role->description }}</div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="p-3 bg-red-50 border border-red-200 rounded-lg">
                                <p class="text-red-700">No roles assigned - user cannot access the system.</p>
                            </div>
                        @endif
                    </div>

                    <!-- Permissions -->
                    @if($user->getAllPermissions()->count() > 0)
                        <div>
                            <h4 class="text-sm font-medium text-gray-700 mb-2">Available Permissions</h4>
                            <div class="grid grid-cols-1 gap-1">
                                @foreach($user->getAllPermissions() as $permission)
                                    <div class="flex items-center p-2 bg-green-50 rounded">
                                        <x-heroicon-o-check-circle class="w-4 h-4 text-green-500 mr-2" />
                                        <span class="text-sm text-gray-700">{{ $permission->name }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- System Properties -->
        <x-system-properties 
            :id="$user->id"
            :created-at="$user->created_at"
            :updated-at="$user->updated_at"
        />
    </div>
@endsection