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