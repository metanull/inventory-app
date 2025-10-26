@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto py-10 px-4 sm:px-6 lg:px-8 space-y-12">
        <header>
            <h1 class="text-3xl font-bold text-gray-900 flex items-center gap-3">
                <span class="inline-flex items-center justify-center p-2 rounded-lg bg-gray-800 text-white">
                    <x-heroicon-o-squares-2x2 class="w-6 h-6" />
                </span>
                Inventory Portal
            </h1>
            <p class="mt-2 text-gray-600 max-w-3xl">Unified Blade management interface mirroring the core domains of the API and SPA client.</p>
        </header>

        

        {{-- Guest Access Group (if unauthenticated) --}}
        @guest
        <section class="space-y-4">
            <h2 class="text-sm font-semibold tracking-wide text-gray-500 uppercase">Getting Started</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <x-ui.card 
                    href="{{ route('login') }}"
                    title="Login"
                    description="Authenticate to access management features."
                    iconColor="emerald"
                    padding="p-5">
                    <x-slot:icon>
                        <x-heroicon-o-lock-closed class="w-6 h-6" />
                    </x-slot:icon>
                    Continue
                </x-ui.card>
            </div>
        </section>
        @endguest

        {{-- Users Without Any Permissions --}}
        @auth
        @php
            $hasAnyPermission = Auth::user()->getAllPermissions()->isNotEmpty();
        @endphp
        @if(!$hasAnyPermission)
        <section class="space-y-4">
            <div class="rounded-lg bg-yellow-50 border border-yellow-200 p-6">
                <div class="flex items-center gap-3 mb-4">
                    <x-heroicon-o-exclamation-triangle class="h-8 w-8 text-yellow-600" />
                    <h2 class="text-lg font-semibold text-yellow-800">Account Under Review</h2>
                </div>
                <p class="text-yellow-700 mb-4">
                    Your account has been successfully created, but it requires verification by an administrator before you can access the system features. 
                </p>
                <p class="text-yellow-700">
                    Please wait for an administrator to grant you the appropriate permissions. You will receive an email notification once your account has been verified and activated.
                </p>
            </div>
        </section>
        @else
        {{-- Inventory Group (if authenticated and has data permissions) --}}
        @can(\App\Enums\Permission::VIEW_DATA->value)
        <section class="space-y-4">
            <h2 class="text-sm font-semibold tracking-wide text-gray-500 uppercase">Inventory</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <x-ui.card 
                    href="{{ route('items.index') }}"
                    title="Items"
                    description="Create, browse and maintain collection item records."
                    entity="items"
                    padding="p-5">
                    <x-slot:icon>
                        <x-heroicon-o-archive-box class="w-6 h-6" />
                    </x-slot:icon>
                    Open
                </x-ui.card>

                <x-ui.card 
                    href="{{ route('item-translations.index') }}"
                    title="Item Translations"
                    description="Manage translations for items across different languages and contexts."
                    entity="item_translations"
                    padding="p-5">
                    <x-slot:icon>
                        <x-heroicon-o-language class="w-6 h-6" />
                    </x-slot:icon>
                    Open
                </x-ui.card>

                <x-ui.card 
                    href="{{ route('partners.index') }}"
                    title="Partners"
                    description="Manage institutions, collaborators and contributors."
                    entity="partners"
                    padding="p-5">
                    <x-slot:icon>
                        <x-heroicon-o-user-group class="w-6 h-6" />
                    </x-slot:icon>
                    Open
                </x-ui.card>

                <x-ui.card 
                    href="{{ route('projects.index') }}"
                    title="Projects"
                    description="Feature flags and visibility of app domains."
                    entity="projects"
                    padding="p-5">
                    <x-slot:icon>
                        <x-heroicon-o-rocket-launch class="w-6 h-6" />
                    </x-slot:icon>
                    Open
                </x-ui.card>

                <x-ui.card 
                    href="{{ route('collections.index') }}"
                    title="Collections"
                    description="Group and present curated item sets."
                    entity="collections"
                    padding="p-5">
                    <x-slot:icon>
                        <x-heroicon-o-rectangle-stack class="w-6 h-6" />
                    </x-slot:icon>
                    Open
                </x-ui.card>

                <x-ui.card 
                    href="{{ route('collection-translations.index') }}"
                    title="Collection Translations"
                    description="Manage translations for collections across different languages and contexts."
                    entity="collection_translations"
                    padding="p-5">
                    <x-slot:icon>
                        <x-heroicon-o-language class="w-6 h-6" />
                    </x-slot:icon>
                    Open
                </x-ui.card>
            </div>
        </section>
        @endcan

        {{-- Reference Group (if authenticated and has data permissions) --}}
        @can(\App\Enums\Permission::VIEW_DATA->value)
        <section class="space-y-4">
            <h2 class="text-sm font-semibold tracking-wide text-gray-500 uppercase">Reference</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <x-ui.card 
                    href="{{ route('countries.index') }}"
                    title="Countries"
                    description="Manage ISO country codes and legacy mappings."
                    entity="countries"
                    padding="p-5">
                    <x-slot:icon>
                        <x-heroicon-o-globe-europe-africa class="w-6 h-6" />
                    </x-slot:icon>
                    Open
                </x-ui.card>

                <x-ui.card 
                    href="{{ route('languages.index') }}"
                    title="Languages"
                    description="Maintain supported languages and default flag."
                    entity="languages"
                    padding="p-5">
                    <x-slot:icon>
                        <x-heroicon-o-language class="w-6 h-6" />
                    </x-slot:icon>
                    Open
                </x-ui.card>

                <x-ui.card 
                    href="{{ route('contexts.index') }}"
                    title="Contexts"
                    description="Default and alternate content contexts."
                    entity="contexts"
                    padding="p-5">
                    <x-slot:icon>
                        <x-heroicon-o-adjustments-horizontal class="w-6 h-6" />
                    </x-slot:icon>
                    Open
                </x-ui.card>

                <x-ui.card 
                    href="{{ route('glossaries.index') }}"
                    title="Glossary"
                    description="Specialized terms, definitions, and spelling variations."
                    entity="glossaries"
                    padding="p-5">
                    <x-slot:icon>
                        <x-heroicon-o-book-open class="w-6 h-6" />
                    </x-slot:icon>
                    Open
                </x-ui.card>

                <x-ui.card 
                    href="{{ route('tags.index') }}"
                    title="Tags"
                    description="Classify and categorize items with flexible tags."
                    entity="tags"
                    padding="p-5">
                    <x-slot:icon>
                        <x-heroicon-o-tag class="w-6 h-6" />
                    </x-slot:icon>
                    Open
                </x-ui.card>

                <x-ui.card 
                    href="{{ route('authors.index') }}"
                    title="Authors"
                    description="Manage content authors and contributors."
                    entity="authors"
                    padding="p-5">
                    <x-slot:icon>
                        <x-heroicon-o-user-circle class="w-6 h-6" />
                    </x-slot:icon>
                    Open
                </x-ui.card>
            </div>
        </section>
        @endcan

        {{-- Image Management Group --}}
        @can(\App\Enums\Permission::VIEW_DATA->value)
        <section class="space-y-4">
            <h2 class="text-sm font-semibold tracking-wide text-gray-500 uppercase">Image Management</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <x-ui.card 
                    href="{{ route('available-images.index') }}"
                    title="Available Images"
                    description="View processed and validated images ready for use in your collection."
                    iconColor="pink"
                    padding="p-5">
                    <x-slot:icon>
                        <x-heroicon-o-photo class="w-6 h-6" />
                    </x-slot:icon>
                    View Images
                </x-ui.card>

                @can(\App\Enums\Permission::CREATE_DATA->value)
                <x-ui.card 
                    href="{{ route('images.upload') }}"
                    title="Upload Images"
                    description="Upload images for validation and processing into the collection."
                    iconColor="indigo"
                    padding="p-5">
                    <x-slot:icon>
                        <x-heroicon-o-cloud-arrow-up class="w-6 h-6" />
                    </x-slot:icon>
                    Upload Images
                </x-ui.card>
                @endcan
            </div>
        </section>
        @endcan
        @endif

        {{-- Administration Group --}}
        @if(auth()->user()->can(\App\Enums\Permission::MANAGE_USERS->value) || auth()->user()->can(\App\Enums\Permission::MANAGE_ROLES->value) || auth()->user()->can(\App\Enums\Permission::MANAGE_SETTINGS->value))
        <section class="space-y-4">
            <h2 class="text-sm font-semibold tracking-wide text-gray-500 uppercase">Administration</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @can(\App\Enums\Permission::MANAGE_USERS->value)
                <x-ui.card 
                    href="{{ route('admin.users.index') }}"
                    title="User Management"
                    description="Manage user accounts and assign roles."
                    iconColor="red"
                    padding="p-5">
                    <x-slot:icon>
                        <x-heroicon-o-users class="w-6 h-6" />
                    </x-slot:icon>
                    Manage
                </x-ui.card>
                @endcan

                @can(\App\Enums\Permission::MANAGE_ROLES->value)
                <x-ui.card 
                    href="{{ route('admin.roles.index') }}"
                    title="Role Management"
                    description="Create and manage roles and permissions."
                    iconColor="purple"
                    padding="p-5">
                    <x-slot:icon>
                        <x-heroicon-o-shield-check class="w-6 h-6" />
                    </x-slot:icon>
                    Manage
                </x-ui.card>
                @endcan

                @can(\App\Enums\Permission::MANAGE_SETTINGS->value)
                <x-ui.card 
                    href="{{ route('settings.index') }}"
                    title="Settings"
                    description="Configure system-wide settings and preferences."
                    iconColor="orange"
                    padding="p-5">
                    <x-slot:icon>
                        <x-heroicon-o-cog-6-tooth class="w-6 h-6" />
                    </x-slot:icon>
                    Configure
                </x-ui.card>
                @endcan

                <x-ui.card 
                    href="{{ route('web.profile.show') }}"
                    title="My Profile"
                    description="View your roles, permissions, and account settings."
                    iconColor="blue"
                    padding="p-5">
                    <x-slot:icon>
                        <x-heroicon-o-user-circle class="w-6 h-6" />
                    </x-slot:icon>
                    View Profile
                </x-ui.card>
            </div>
        </section>
        @endif
        @endauth

        {{-- Resources & Tools Group --}}
        <section class="space-y-4">
            <h2 class="text-sm font-semibold tracking-wide text-gray-500 uppercase">Resources &amp; Tools</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @auth
                    <x-ui.card 
                        href="{{ url('/docs/api') }}"
                        title="API Documentation"
                        description="REST endpoints, schemas and integration notes."
                        iconColor="indigo"
                        padding="p-5">
                        <x-slot:icon>
                            <x-heroicon-o-book-open class="w-6 h-6" />
                        </x-slot:icon>
                        Browse
                    </x-ui.card>
                @endauth

                @if(config('interface.show_spa_link'))
                <x-ui.card 
                    href="{{ url('/cli') }}"
                    title="SPA Client"
                    description="Vue.js client showcasing reactive workflows."
                    iconColor="purple"
                    padding="p-5">
                    <x-slot:icon>
                        <x-heroicon-o-window class="w-6 h-6" />
                    </x-slot:icon>
                    Launch
                </x-ui.card>
                @endif

                <x-ui.card 
                    href="https://github.com/metanull/inventory-app"
                    title="Source Code"
                    description="Explore repository & contributions."
                    iconColor="gray"
                    padding="p-5"
                    target="_blank">
                    <x-slot:icon>
                        <x-heroicon-o-code-bracket class="w-6 h-6" />
                    </x-slot:icon>
                    Open
                </x-ui.card>

                <x-ui.card 
                    href="https://metanull.github.io/inventory-app"
                    title="Project Docs"
                    description="Development guidelines & architecture."
                    iconColor="sky"
                    padding="p-5"
                    target="_blank">
                    <x-slot:icon>
                        <x-heroicon-o-document-text class="w-6 h-6" />
                    </x-slot:icon>
                    Read
                </x-ui.card>
            </div>
        </section>
    </div>
@endsection
