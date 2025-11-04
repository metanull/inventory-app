@extends('layouts.app')

@section('content')
    @php($c = $entityColor('users'))
    
    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <x-entity.header entity="users" title="Create User">
            <x-slot name="action">
                <x-ui.button 
                    href="{{ route('admin.users.index') }}" 
                    variant="secondary">
                    Back to Users
                </x-ui.button>
            </x-slot>
        </x-entity.header>

        <div class="bg-white overflow-hidden shadow sm:rounded-lg">
            <div class="p-6">
                <form method="POST" action="{{ route('admin.users.store') }}">
                    @csrf
                    
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
                                    type="text" 
                                    :value="old('name')" 
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
                                    :value="old('email')" 
                                    required 
                                    class="mt-1"
                                />
                                @error('email')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <x-ui.alert type="info" entity="users">
                                <div>
                                    <h3 class="text-sm font-medium">
                                        {{ __('Password Generation') }}
                                    </h3>
                                    <div class="mt-2 text-sm">
                                        <p>{{ __('A secure password will be automatically generated for this user. After creating the user, you will see the generated password that you can share with them.') }}</p>
                                    </div>
                                </div>
                            </x-ui.alert>
                        </div>

                        <!-- Role Assignment -->
                        <div class="space-y-4">
                            <h3 class="text-lg font-medium text-gray-900">{{ __('Role Assignment') }}</h3>
                            
                            @if($roles->count() > 0)
                                <x-form.checkbox-group name="roles[]" label="">
                                    @foreach($roles as $role)
                                        <x-form.checkbox-simple
                                            name="roles[]"
                                            :value="$role->id"
                                            :id="'role_' . $role->id"
                                            :checked="in_array($role->id, old('roles', []))"
                                            :label="$role->name"
                                            :help="$role->description"
                                        />
                                    @endforeach
                                </x-form.checkbox-group>
                            @else
                                <p class="text-gray-500">{{ __('No roles available.') }}</p>
                            @endif
                            
                            @error('roles')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end gap-3">
                        <x-ui.button 
                            href="{{ route('admin.users.index') }}" 
                            variant="secondary">
                            Cancel
                        </x-ui.button>
                        <x-ui.button 
                            type="submit" 
                            variant="primary"
                            entity="users">
                            Create User
                        </x-ui.button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection