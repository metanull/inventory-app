<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('User Details') }}: {{ $user->name }}
            </h2>
            <div class="flex space-x-2">
                @can('manage users')
                    <a href="{{ route('admin.users.edit', $user) }}" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                        {{ __('Edit User') }}
                    </a>
                @endcan
                <a href="{{ route('admin.users.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    {{ __('Back to Users') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- User Information -->
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('User Information') }}</h3>
                        
                        <dl class="space-y-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">{{ __('Name') }}</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $user->name }}</dd>
                            </div>
                            
                            <div>
                                <dt class="text-sm font-medium text-gray-500">{{ __('Email') }}</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $user->email }}</dd>
                            </div>
                            
                            <div>
                                <dt class="text-sm font-medium text-gray-500">{{ __('Email Verified') }}</dt>
                                <dd class="mt-1">
                                    @if($user->email_verified_at)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            {{ __('Verified') }} ({{ $user->email_verified_at->format('M j, Y H:i') }})
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            {{ __('Not Verified') }}
                                        </span>
                                    @endif
                                </dd>
                            </div>
                            
                            <div>
                                <dt class="text-sm font-medium text-gray-500">{{ __('Created') }}</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $user->created_at->format('M j, Y H:i') }}</dd>
                            </div>
                            
                            <div>
                                <dt class="text-sm font-medium text-gray-500">{{ __('Last Updated') }}</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $user->updated_at->format('M j, Y H:i') }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <!-- Roles and Permissions -->
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-medium text-gray-900">{{ __('Roles & Permissions') }}</h3>
                            @can('assign roles')
                                <a href="{{ route('admin.users.roles', $user) }}" class="text-blue-600 hover:text-blue-900">
                                    {{ __('Manage Roles') }}
                                </a>
                            @endcan
                        </div>
                        
                        <!-- Roles -->
                        <div class="mb-6">
                            <h4 class="text-sm font-medium text-gray-700 mb-2">{{ __('Assigned Roles') }}</h4>
                            @if($user->roles->count() > 0)
                                <div class="space-y-2">
                                    @foreach($user->roles as $role)
                                        <div class="flex items-center justify-between p-3 bg-blue-50 rounded-lg">
                                            <div>
                                                <div class="font-medium text-blue-900">{{ $role->name }}</div>
                                                @if($role->description)
                                                    <div class="text-sm text-blue-700">{{ $role->description }}</div>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="p-3 bg-red-50 border border-red-200 rounded-lg">
                                    <p class="text-red-700">{{ __('No roles assigned - user cannot access the system.') }}</p>
                                </div>
                            @endif
                        </div>

                        <!-- Permissions -->
                        @if($user->getAllPermissions()->count() > 0)
                            <div>
                                <h4 class="text-sm font-medium text-gray-700 mb-2">{{ __('Available Permissions') }}</h4>
                                <div class="grid grid-cols-1 gap-1">
                                    @foreach($user->getAllPermissions() as $permission)
                                        <div class="flex items-center p-2 bg-green-50 rounded">
                                            <svg class="w-4 h-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                            </svg>
                                            <span class="text-sm text-gray-700">{{ $permission->name }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>