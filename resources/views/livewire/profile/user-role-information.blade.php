<x-action-section>
    <x-slot name="title">
        {{ __('User Roles & Permissions') }}
    </x-slot>

    <x-slot name="description">
        {{ __('View your assigned roles and permissions.') }}
    </x-slot>

    <x-slot name="content">
        @if($roles->count() > 0)
            <div class="space-y-4">
                <!-- Roles Section -->
                <div>
                    <h3 class="text-lg font-medium text-gray-900">{{ __('Assigned Roles') }}</h3>
                    <div class="mt-2 space-y-2">
                        @foreach($roles as $role)
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div>
                                    <div class="font-medium text-gray-900">{{ $role->name }}</div>
                                    @if($role->description)
                                        <div class="text-sm text-gray-600">{{ $role->description }}</div>
                                    @endif
                                </div>
                                <x-ui.badge color="green" variant="pill">
                                    {{ __('Active') }}
                                </x-ui.badge>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Permissions Section -->
                @if($permissions->count() > 0)
                    <div>
                        <h3 class="text-lg font-medium text-gray-900">{{ __('Available Permissions') }}</h3>
                        <div class="mt-2 grid grid-cols-1 md:grid-cols-2 gap-2">
                            @foreach($permissions as $permission)
                                <div class="flex items-center p-2 bg-blue-50 rounded">
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
        @else
            <div class="flex items-center justify-center p-6 bg-yellow-50 border border-yellow-200 rounded-lg">
                <div class="text-center">
                    <svg class="w-8 h-8 text-yellow-500 mx-auto mb-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                    </svg>
                    <h3 class="text-lg font-medium text-yellow-800">{{ __('No Roles Assigned') }}</h3>
                    <p class="text-yellow-700">{{ __('You have no roles assigned. Please contact an administrator to get access to the system.') }}</p>
                </div>
            </div>
        @endif
    </x-slot>
</x-action-section>
