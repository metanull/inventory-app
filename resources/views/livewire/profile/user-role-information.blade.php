<x-action-section>
    <x-slot name="title">
        {{ __('User Roles & Permissions') }}
    </x-slot>

    <x-slot name="description">
        {{ __('View your assigned roles and permissions.') }}
    </x-slot>

    <x-slot name="content">
        @php($uc = $entityColor('users'))
        @if($roles->count() > 0)
            <div class="space-y-4">
                <!-- Roles Section -->
                <div>
                    <h3 class="text-lg font-medium text-gray-900">{{ __('Assigned Roles') }}</h3>
                    <div class="mt-2 space-y-2">
                        @foreach($roles as $role)
                            <div class="flex items-center justify-between p-3 {{ $uc['bg'] }} rounded-lg">
                                <div>
                                    <div class="font-medium text-gray-900">{{ $role->name }}</div>
                                    @if($role->description)
                                        <div class="text-sm text-gray-600">{{ $role->description }}</div>
                                    @endif
                                </div>
                                <x-ui.badge entity="users" variant="pill">
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
                                <div class="flex items-center p-2 {{ $uc['bg'] }} rounded">
                                    <x-heroicon-o-check-circle class="w-4 h-4 {{ $uc['text'] }} mr-2" />
                                    <span class="text-sm text-gray-700">{{ $permission->name }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        @else
            <x-ui.alert type="warning" entity="users">
                <div class="text-center">
                    <x-heroicon-o-exclamation-triangle class="w-8 h-8 mx-auto mb-2" />
                    <h3 class="text-lg font-medium">{{ __('No Roles Assigned') }}</h3>
                    <p>{{ __('You have no roles assigned. Please contact an administrator to get access to the system.') }}</p>
                </div>
            </x-ui.alert>
        @endif
    </x-slot>
</x-action-section>
