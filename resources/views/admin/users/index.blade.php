@extends('layouts.app')

@section('content')
    @php
        $c = $entityColor('users');
        $currentQuery = $listState->query();
        $formQuery = $listState->query(['q', 'role', 'page']);
        $sortQuery = $listState->query(['sort', 'direction', 'page']);
    @endphp

    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <x-entity.header entity="users" title="User Management">
            @can(\App\Enums\Permission::MANAGE_USERS->value)
                <x-ui.button
                    href="{{ route('admin.users.create') }}"
                    variant="primary"
                    entity="users"
                    icon="plus"
                >
                    Create User
                </x-ui.button>
            @endcan
        </x-entity.header>

        @if (session('success'))
            <x-ui.alert type="success" :message="session('success')" entity="users" />
        @endif

        @if (session('error'))
            <x-ui.alert type="error" :message="session('error')" entity="users" />
        @endif

        @if(session('generated_password'))
            <x-ui.alert type="success" entity="users" :dismissible="false">
                <div class="flex">
                    <div class="ml-3 flex-1">
                        <h3 class="text-sm font-medium text-green-800">
                            {{ __('Password Generated Successfully') }}
                        </h3>
                        <div class="mt-2 text-sm text-green-700">
                            <p><strong>{{ __('User:') }}</strong> {{ session('user_name') }} ({{ session('user_email') }})</p>
                            <p><strong>{{ __('Generated Password:') }}</strong> 
                                <span class="font-mono bg-gray-100 px-2 py-1 rounded text-gray-900 select-all" id="generated-password">{{ session('generated_password') }}</span>
                            </p>
                            <p class="mt-2 text-xs">{{ __('Please copy this password and share it securely with the user. This password will not be shown again.') }}</p>
                        </div>
                    </div>
                    <div class="ml-4 shrink-0">
                        <button type="button" class="bg-green-100 rounded-md inline-flex text-green-400 hover:text-green-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500" onclick="copyToClipboard('{{ session('generated_password') }}')">
                            <span class="sr-only">{{ __('Copy password') }}</span>
                            <x-heroicon-o-clipboard class="h-5 w-5" />
                        </button>
                    </div>
                </div>
            </x-ui.alert>
        @endif

        <div class="space-y-6">
            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <x-list.search-form
                    :action="route('admin.users.index')"
                    :query="$formQuery"
                    :search="$listState->search"
                    placeholder="Search users..."
                    :clear-url="route('admin.users.index')"
                >
                    <div>
                        <select name="role" class="rounded-md border-gray-300 shadow-sm {{ $c['focus'] }}">
                            <option value="">All Roles</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->name }}" @selected(($listState->filters['role'] ?? null) === $role->name)>
                                    {{ $role->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </x-list.search-form>
            </div>

            <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                <table class="min-w-full divide-y divide-gray-200">
                    <x-table.header>
                        <x-list.sort-link label="User" field="name" :current-sort="$listState->sort" :current-direction="$listState->direction" :url="route('admin.users.index')" :query="$sortQuery" />
                        <x-list.sort-link label="Email" field="email" :current-sort="$listState->sort" :current-direction="$listState->direction" :url="route('admin.users.index')" :query="$sortQuery" class="hidden md:table-cell" />
                        <x-table.header-cell>Roles</x-table.header-cell>
                        <x-table.header-cell>MFA Status</x-table.header-cell>
                        <x-list.sort-link label="Email Verified" field="email_verified_at" :current-sort="$listState->sort" :current-direction="$listState->direction" :url="route('admin.users.index')" :query="$sortQuery" class="hidden lg:table-cell" />
                        <x-list.sort-link label="Created" field="created_at" :current-sort="$listState->sort" :current-direction="$listState->direction" :url="route('admin.users.index')" :query="$sortQuery" class="hidden lg:table-cell" />
                        <x-table.header-cell class="text-right"><span class="sr-only">Actions</span></x-table.header-cell>
                    </x-table.header>

                    <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse($users as $user)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                    <a href="{{ route('admin.users.show', $user) }}" class="text-gray-900 hover:text-blue-700 hover:underline">{{ $user->name }}</a>
                                </td>
                                <td class="hidden md:table-cell px-4 py-3 text-sm text-gray-500">{{ $user->email }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="flex flex-wrap gap-1">
                                        @forelse($user->roles as $role)
                                            <x-ui.badge color="blue" variant="pill">{{ $role->name }}</x-ui.badge>
                                        @empty
                                            <x-ui.badge color="red" variant="pill">{{ __('No Role') }}</x-ui.badge>
                                        @endforelse
                                    </div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    @if($user->hasEnabledTwoFactorAuthentication())
                                        <x-ui.badge color="green" variant="pill" title="{{ __('TOTP 2FA Enabled') }}">
                                            <x-heroicon-o-lock-closed class="w-3 h-3 mr-1" />
                                            {{ __('Enabled') }}
                                        </x-ui.badge>
                                    @else
                                        <x-ui.badge color="gray" variant="pill">{{ __('Disabled') }}</x-ui.badge>
                                    @endif
                                </td>
                                <td class="hidden lg:table-cell px-4 py-3 whitespace-nowrap">
                                    @if($user->email_verified_at)
                                        <x-ui.badge color="green" variant="pill">{{ __('Verified') }}</x-ui.badge>
                                    @else
                                        <x-ui.badge color="yellow" variant="pill">{{ __('Pending') }}</x-ui.badge>
                                    @endif
                                </td>
                                <td class="hidden lg:table-cell px-4 py-3 whitespace-nowrap text-sm text-gray-500">{{ optional($user->created_at)->format('M j, Y') }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex justify-end gap-2">
                                        <x-ui.button href="{{ route('admin.users.show', $user) }}" variant="edit" size="sm" entity="users" icon="eye">View</x-ui.button>
                                        @can(\App\Enums\Permission::MANAGE_USERS->value)
                                            <x-ui.button href="{{ route('admin.users.edit', $user) }}" variant="warning" size="sm" icon="pencil">Edit</x-ui.button>
                                            @if($user->id !== auth()->id())
                                                <x-ui.confirm-button action="{{ route('admin.users.destroy', $user) }}" confirmMessage="Are you sure you want to delete this user?" variant="danger" size="sm" icon="trash">Delete</x-ui.confirm-button>
                                            @endif
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-10 text-center text-sm text-gray-500">{{ __('No users found.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                </div>

            <x-list.pagination :paginator="$users" :action="route('admin.users.index')" :query="$currentQuery" :current-per-page="$listState->perPage" entity="users" />
            </div>
        </div>
    </div>
    
    <script>
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(function() {
                // Show temporary feedback
                const button = event.target.closest('button');
                const originalHTML = button.innerHTML;
                button.innerHTML = '<svg class="h-5 w-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>';
                setTimeout(function() {
                    button.innerHTML = originalHTML;
                }, 2000);
            });
        }
    </script>
@endsection