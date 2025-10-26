@extends('layouts.app')

@section('content')
    <div class="max-w-3xl mx-auto py-8 px-4 sm:px-6 lg:px-8 space-y-8">
        @php($c = $entityColor('roles'))
        
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

        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
            <div class="p-6">
                <form method="POST" action="{{ route('admin.roles.update', $role) }}">
                    @csrf
                    @method('PUT')

                    <!-- Role Name -->
                    <div class="mb-4">
                        <label for="name" class="block text-sm font-medium text-gray-700">Role Name *</label>
                        <input type="text" name="name" id="name" value="{{ old('name', $role->name) }}" required
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 @error('name') border-red-500 @enderror">
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Description -->
                    <div class="mb-4">
                        <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea name="description" id="description" rows="3"
                                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 @error('description') border-red-500 @enderror">{{ old('description', $role->description) }}</textarea>
                        @error('description')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Permission Assignment -->
                    <div class="mb-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Assign Permissions</h3>
                        
                        @if($permissions->count() > 0)
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                @foreach($permissions as $permission)
                                    <label class="flex items-start">
                                        <input type="checkbox" 
                                               name="permissions[]" 
                                               value="{{ $permission->id }}"
                                               {{ $role->hasPermissionTo($permission->name) || in_array($permission->id, old('permissions', [])) ? 'checked' : '' }}
                                               class="mt-1 rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                        <div class="ml-3">
                                            <span class="text-sm font-medium text-gray-700">{{ $permission->name }}</span>
                                            @if($permission->description)
                                                <p class="text-xs text-gray-500 mt-1">{{ $permission->description }}</p>
                                            @endif
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        @else
                            <p class="text-gray-500 italic">No permissions available.</p>
                        @endif
                        @error('permissions')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Submit Button -->
                    <div class="flex items-center justify-end space-x-2">
                        <x-ui.button 
                            href="{{ route('admin.roles.index') }}" 
                            variant="secondary"
                            entity="roles">
                            Cancel
                        </x-ui.button>
                        <x-ui.button 
                            type="submit" 
                            variant="primary"
                            entity="roles">
                            Update Role
                        </x-ui.button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
