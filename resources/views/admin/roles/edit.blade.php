@extends('layouts.app')

@section('content')
    @php
        $c = $entityColor('roles');
        $selectedPermissions = $role->permissions->pluck('id')->toArray();
    @endphp
    
    <div class="max-w-3xl mx-auto py-8 px-4 sm:px-6 lg:px-8 space-y-8">
        <div>
            <a href="{{ route('admin.roles.index') }}" class="text-sm {{ $c['accentLink'] }}">&larr; Back to Roles</a>
        </div>
        
        <x-entity.header entity="roles" :title="'Edit Role: ' . $role->name">
            <x-ui.button 
                href="{{ route('admin.roles.show', $role) }}" 
                variant="secondary"
                entity="roles"
                icon="eye">
                View Role
            </x-ui.button>
        </x-entity.header>

        <div class="bg-white overflow-hidden shadow sm:rounded-lg">
            <div class="p-6">
                <form method="POST" action="{{ route('admin.roles.update', $role) }}">
                    @csrf
                    @method('PUT')

                    <div class="space-y-6">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">
                                Role Name <span class="text-red-500">*</span>
                            </label>
                            <x-form.input 
                                name="name" 
                                :value="old('name', $role->name)" 
                                required 
                            />
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700">
                                Description
                            </label>
                            <x-form.textarea 
                                name="description" 
                                :value="old('description', $role->description)" 
                                rows="3"
                            />
                            @error('description')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Permission Assignment -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Assign Permissions</h3>
                            
                            @if($permissions->count() > 0)
                                <x-form.checkbox-list 
                                    :items="$permissions"
                                    name="permissions[]"
                                    :selected="$selectedPermissions"
                                    entity="roles"
                                />
                            @else
                                <p class="text-gray-500 italic">No permissions available.</p>
                            @endif
                            @error('permissions')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <x-form.actions 
                        :cancel-route="route('admin.roles.index')" 
                        entity="roles"
                        save-label="Update Role"
                    />
                </form>
            </div>
        </div>
    </div>
@endsection
