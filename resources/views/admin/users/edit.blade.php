<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Edit User') }}: {{ $user->name }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('admin.users.show', $user) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    {{ __('View User') }}
                </a>
                <a href="{{ route('admin.users.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    {{ __('Back to Users') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('admin.users.update', $user) }}">
                        @csrf
                        @method('PUT')
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Basic Information -->
                            <div class="space-y-4">
                                <h3 class="text-lg font-medium text-gray-900">{{ __('Basic Information') }}</h3>
                                
                                <div>
                                    <label for="name" class="block text-sm font-medium text-gray-700">{{ __('Name') }}</label>
                                    <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    @error('name')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="email" class="block text-sm font-medium text-gray-700">{{ __('Email') }}</label>
                                    <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" required
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    @error('email')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="space-y-4">
                                    <h4 class="text-sm font-medium text-gray-900">{{ __('Password Management') }}</h4>
                                    <div class="flex items-start">
                                        <div class="flex items-center h-5">
                                            <input type="checkbox" name="generate_new_password" value="1" id="generate_new_password"
                                                class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
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
                                    <div class="p-4 bg-yellow-50 border border-yellow-200 rounded-md">
                                        <div class="flex">
                                            <div class="shrink-0">
                                                <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                                </svg>
                                            </div>
                                            <div class="ml-3">
                                                <h3 class="text-sm font-medium text-yellow-800">
                                                    {{ __('Cannot Edit Own Roles') }}
                                                </h3>
                                                <div class="mt-2 text-sm text-yellow-700">
                                                    <p>{{ __('You cannot modify your own role assignments for security reasons. Please ask another administrator to make changes to your roles.') }}</p>
                                                </div>
                                                <div class="mt-3">
                                                    <p class="text-sm font-medium text-yellow-800">{{ __('Your Current Roles:') }}</p>
                                                    <div class="mt-2 flex flex-wrap gap-2">
                                                        @forelse($user->roles as $role)
                                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800 border border-yellow-300">
                                                                {{ $role->name }}
                                                            </span>
                                                        @empty
                                                            <span class="text-sm text-yellow-700">{{ __('No roles assigned') }}</span>
                                                        @endforelse
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <div class="space-y-2">
                                        @foreach($roles as $role)
                                            <label class="flex items-center">
                                                <input type="checkbox" 
                                                       name="roles[]" 
                                                       value="{{ $role->id }}"
                                                       {{ in_array($role->id, old('roles', $user->roles->pluck('id')->toArray())) ? 'checked' : '' }}
                                                       class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
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

                        <div class="mt-6 flex items-center justify-end space-x-3">
                            <a href="{{ route('admin.users.show', $user) }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                                {{ __('Cancel') }}
                            </a>
                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                {{ __('Update User') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>