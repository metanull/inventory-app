@extends('layouts.app')

@section('content')
    @php($c = $entityColor('users'))
    
    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <x-entity.header entity="users" :title="'Edit User: ' . $user->name">
            <x-slot name="action">
                <div class="flex gap-2">
                    <x-ui.button 
                        href="{{ route('admin.users.show', $user) }}" 
                        variant="secondary">
                        View User
                    </x-ui.button>
                    <x-ui.button 
                        href="{{ route('admin.users.index') }}" 
                        variant="secondary">
                        Back to Users
                    </x-ui.button>
                </div>
            </x-slot>
        </x-entity.header>

        <div class="bg-white overflow-hidden shadow sm:rounded-lg">
            <div class="p-6">
                <form method="POST" action="{{ route('admin.users.update', $user) }}">
                    @csrf
                    @method('PUT')
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Basic Information -->
                        <div class="space-y-4">
                            <h3 class="text-lg font-medium text-gray-900">{{ __('Basic Information') }}</h3>
                            
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700">
                                    {{ __('Name') }} <span class="text-red-500">*</span>
                                </label>
                                <x-form.input 
                                    name="name" 
                                    :value="old('name', $user->name)" 
                                    required 
                                    class="mt-1"
                                />
                                @error('name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700">
                                    {{ __('Email') }} <span class="text-red-500">*</span>
                                </label>
                                <x-form.input 
                                    name="email" 
                                    type="email"
                                    :value="old('email', $user->email)" 
                                    required 
                                    class="mt-1"
                                />
                                @error('email')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="space-y-4">
                                <h4 class="text-sm font-medium text-gray-900">{{ __('Password Management') }}</h4>
                                <div class="flex items-start">
                                    <div class="flex items-center h-5">
                                        <input type="checkbox" name="generate_new_password" value="1" id="generate_new_password"
                                            class="rounded border-gray-300 text-{{ $c['name'] }}-600 focus:ring-{{ $c['name'] }}-500 h-4 w-4">
                                    </div>
                                    <div class="ml-3">
                                        <label for="generate_new_password" class="font-medium text-gray-700">{{ __('Generate New Password') }}</label>
                                        <p class="text-sm text-gray-500">{{ __('Check this box to generate a new secure password for this user. The generated password will be shown after saving.') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Role Assignment -->
                        <div class="space-y-4">
                            <h3 class="text-lg font-medium text-gray-900">{{ __('Role Assignment') }}</h3>
                            
                            @if($isEditingSelf)
                                <x-ui.alert type="warning" entity="users">
                                    <div class="flex">
                                        <div class="ml-3">
                                            <h3 class="text-sm font-medium">
                                                {{ __('Cannot Edit Own Roles') }}
                                            </h3>
                                            <div class="mt-2 text-sm">
                                                <p>{{ __('You cannot modify your own role assignments for security reasons. Please ask another administrator to make changes to your roles.') }}</p>
                                            </div>
                                            <div class="mt-3">
                                                <p class="text-sm font-medium">{{ __('Your Current Roles:') }}</p>
                                                <div class="mt-2 flex flex-wrap gap-2">
                                                    @forelse($user->roles as $role)
                                                        <x-ui.badge entity="users">{{ $role->name }}</x-ui.badge>
                                                    @empty
                                                        <span class="text-sm">{{ __('No roles assigned') }}</span>
                                                    @endforelse
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </x-ui.alert>
                            @else
                                <div class="space-y-2">
                                    @foreach($roles as $role)
                                        <label class="flex items-center">
                                            <input type="checkbox" 
                                                   name="roles[]" 
                                                   value="{{ $role->id }}"
                                                   {{ in_array($role->id, old('roles', $user->roles->pluck('id')->toArray())) ? 'checked' : '' }}
                                                   class="rounded border-gray-300 text-{{ $c['name'] }}-600 shadow-sm focus:border-{{ $c['name'] }}-300 focus:ring focus:ring-{{ $c['name'] }}-200 focus:ring-opacity-50">
                                            <span class="ml-2 text-sm text-gray-700">{{ $role->name }}</span>
                                            @if($role->permissions->count() > 0)
                                                <span class="ml-2 text-xs text-gray-500">
                                                    ({{ $role->permissions->pluck('name')->implode(', ') }})
                                                </span>
                                            @endif
                                        </label>
                                    @endforeach
                                    @error('roles')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            @endif

                            <!-- Email Verification Management -->
                            <div class="mt-6 pt-6 border-t border-gray-200">
                                <h4 class="text-md font-medium text-gray-900 mb-3">{{ __('Email Verification') }}</h4>
                                
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">
                                            {{ __('Current Status') }}: 
                                            @if($user->hasVerifiedEmail())
                                                <span class="text-green-600">✅ {{ __('Verified') }}</span>
                                            @else
                                                <span class="text-red-600">❌ {{ __('Not Verified') }}</span>
                                            @endif
                                        </p>
                                        @if($user->hasVerifiedEmail())
                                            <p class="text-xs text-gray-500 mt-1">
                                                {{ __('Verified at') }}: {{ $user->email_verified_at->format('Y-m-d H:i:s') }}
                                            </p>
                                        @endif
                                    </div>
                                    <div class="flex space-x-2">
                                        @if($user->hasVerifiedEmail())
                                            <label class="flex items-center">
                                                <input type="checkbox" name="unverify_email" value="1"
                                                       class="rounded border-gray-300 text-red-600 shadow-sm focus:border-red-300 focus:ring focus:ring-red-200 focus:ring-opacity-50">
                                                <span class="ml-2 text-sm text-red-600">{{ __('Remove verification') }}</span>
                                            </label>
                                        @else
                                            <label class="flex items-center">
                                                <input type="checkbox" name="verify_email" value="1"
                                                       class="rounded border-gray-300 text-green-600 shadow-sm focus:border-green-300 focus:ring focus:ring-green-200 focus:ring-opacity-50">
                                                <span class="ml-2 text-sm text-green-600">{{ __('Mark as verified') }}</span>
                                            </label>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <x-form.actions 
                        :cancel-route="route('admin.users.show', $user)" 
                        entity="users"
                        save-label="Update User"
                    />
                </form>
            </div>
        </div>
    </div>
@endsection