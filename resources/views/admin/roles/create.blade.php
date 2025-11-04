@extends('layouts.app')

@section('content')
    <div class="max-w-3xl mx-auto py-8 px-4 sm:px-6 lg:px-8 space-y-8">
        @php($c = $entityColor('roles'))
        
        <div>
            <a href="{{ route('admin.roles.index') }}" class="text-sm {{ $c['accentLink'] }}">&larr; Back to Roles</a>
        </div>
        
        <x-entity.header entity="roles" title="Create New Role" />

        <div class="bg-white overflow-hidden shadow sm:rounded-lg">
            <div class="p-6">
                <form method="POST" action="{{ route('admin.roles.store') }}">
                    @csrf

                    <div class="space-y-6">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">
                                Role Name <span class="text-red-500">*</span>
                            </label>
                            <x-form.input 
                                name="name" 
                                :value="old('name')" 
                                required 
                                class="mt-1"
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
                                :value="old('description')" 
                                rows="3"
                                class="mt-1"
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
                                    :selected="old('permissions', [])"
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
                        save-label="Create Role"
                    />
                </form>
            </div>
        </div>
    </div>
@endsection
