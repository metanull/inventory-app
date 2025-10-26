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

        <div class="bg-white overflow-hidden shadow sm:rounded-lg">
            <div class="p-6">
                <form method="POST" action="{{ route('admin.roles.update', $role) }}">
                    @csrf
                    @method('PUT')

                    <div class="space-y-6">
                        <x-form.field 
                            label="Role Name" 
                            name="name" 
                            :value="old('name', $role->name)" 
                            required 
                        />

                        <x-form.field 
                            label="Description" 
                            name="description" 
                            type="textarea"
                            :value="old('description', $role->description)" 
                            rows="3"
                        />

                        <!-- Permission Assignment -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Assign Permissions</h3>
                            
                            @if($permissions->count() > 0)
                                @php
                                    $selectedPermissions = $role->permissions->pluck('id')->toArray();
                                @endphp
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
                        submit-text="Update Role"
                    />
                </form>
            </div>
        </div>
    </div>
@endsection
