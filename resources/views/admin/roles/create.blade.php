@extends('layouts.app')

@section('content')
    <div class="max-w-3xl mx-auto py-8 px-4 sm:px-6 lg:px-8 space-y-8">
        @php
            $c = $entityColor('roles');
            $permCheckboxTextColor = 'text-' . $c['name'] . '-600';
            $permCheckboxFocusBorder = 'focus:border-' . $c['name'] . '-300';
            $permCheckboxFocusRing = 'focus:ring-' . $c['name'] . '-200';
            $permCheckboxClass = 'mt-1 rounded border-gray-300 ' . $permCheckboxTextColor . ' shadow-sm ' . $permCheckboxFocusBorder . ' focus:ring ' . $permCheckboxFocusRing . ' focus:ring-opacity-50';
        @endphp
        
        <div>
            <a href="{{ route('admin.roles.index') }}" class="text-sm {{ $c['accentLink'] }}">&larr; Back to Roles</a>
        </div>
        
        <x-entity.header entity="roles" title="Create New Role" />

        <div class="bg-white overflow-hidden shadow sm:rounded-lg">
            <div class="p-6">
                <form method="POST" action="{{ route('admin.roles.store') }}">
                    @csrf

                    <div class="space-y-6">
                        <x-form.field 
                            label="Role Name" 
                            name="name" 
                            :value="old('name')" 
                            required 
                        />

                        <x-form.field 
                            label="Description" 
                            name="description" 
                            type="textarea"
                            :value="old('description')" 
                            rows="3"
                        />

                        <!-- Permission Assignment -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Assign Permissions</h3>
                            
                            @if($permissions->count() > 0)
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    @foreach($permissions as $permission)
                                        <label class="flex items-start">
                                            <input type="checkbox" 
                                                   name="permissions[]" 
                                                   value="{{ $permission->id }}"
                                                   {{ in_array($permission->id, old('permissions', [])) ? 'checked' : '' }}
                                                   class="{{ $permCheckboxClass }}">
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
                    </div>

                    <x-form.actions 
                        :cancel-route="route('admin.roles.index')" 
                        entity="roles"
                        submit-text="Create Role"
                    />
                </form>
            </div>
        </div>
    </div>
@endsection
