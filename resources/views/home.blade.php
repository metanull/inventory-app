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
                <a href="{{ route('login') }}" class="group rounded-xl border border-gray-200 bg-white p-5 hover:shadow transition flex flex-col">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <span class="p-2 rounded-md bg-emerald-50 text-emerald-600 group-hover:opacity-90">
                                <x-heroicon-o-lock-closed class="w-6 h-6" />
                            </span>
                            <h3 class="text-lg font-semibold text-gray-900">Login</h3>
                        </div>
                        <x-heroicon-o-eye class="w-5 h-5 text-gray-400 group-hover:text-emerald-600" />
                    </div>
                    <p class="text-sm text-gray-600 flex-1">Authenticate to access management features.</p>
                    <span class="mt-4 inline-flex items-center text-sm font-medium text-emerald-600 group-hover:underline">Continue &rarr;</span>
                </a>
            </div>
        </section>
        @endguest

        {{-- Primary Domain Group (if authenticated) --}}
        @auth
        <section class="space-y-4">
            <h2 class="text-sm font-semibold tracking-wide text-gray-500 uppercase">Primary Domains</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @php($ic = $entityColor('items'))
                <a href="{{ route('items.index') }}" class="group rounded-xl border border-gray-200 bg-white p-5 hover:shadow transition flex flex-col ring-1 ring-transparent hover:ring-{{ $ic['base'] }}/40">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <span class="p-2 rounded-md {{ $ic['bg'] ?? 'bg-teal-50' }} {{ $ic['text'] ?? 'text-teal-600' }} group-hover:opacity-90">
                                <x-heroicon-o-archive-box class="w-6 h-6" />
                            </span>
                            <h3 class="text-lg font-semibold text-gray-900">Items</h3>
                        </div>
                        <x-heroicon-o-eye class="w-5 h-5 text-gray-400 group-hover:{{ $ic['text'] ?? 'text-teal-600' }}" />
                    </div>
                    <p class="text-sm text-gray-600 flex-1">Create, browse and maintain collection item records.</p>
                    <span class="mt-4 inline-flex items-center text-sm font-medium {{ $ic['text'] ?? 'text-teal-600' }} group-hover:underline">Open &rarr;</span>
                </a>

                @php($pc = $entityColor('partners'))
                <a href="{{ route('partners.index') }}" class="group rounded-xl border border-gray-200 bg-white p-5 hover:shadow transition flex flex-col ring-1 ring-transparent hover:ring-{{ $pc['base'] }}/40">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <span class="p-2 rounded-md {{ $pc['bg'] ?? 'bg-yellow-50' }} {{ $pc['text'] ?? 'text-yellow-600' }} group-hover:opacity-90">
                                <x-heroicon-o-user-group class="w-6 h-6" />
                            </span>
                            <h3 class="text-lg font-semibold text-gray-900">Partners</h3>
                        </div>
                        <x-heroicon-o-eye class="w-5 h-5 text-gray-400 group-hover:{{ $pc['text'] ?? 'text-yellow-600' }}" />
                    </div>
                    <p class="text-sm text-gray-600 flex-1">Manage institutions, collaborators and contributors.</p>
                    <span class="mt-4 inline-flex items-center text-sm font-medium {{ $pc['text'] ?? 'text-yellow-600' }} group-hover:underline">Open &rarr;</span>
                </a>

                @php($cc = $entityColor('countries'))
                <a href="{{ route('countries.index') }}" class="group rounded-xl border border-gray-200 bg-white p-5 hover:shadow transition flex flex-col ring-1 ring-transparent hover:ring-{{ $cc['base'] }}/40">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <span class="p-2 rounded-md bg-indigo-50 text-indigo-600 group-hover:opacity-90">
                                <x-heroicon-o-globe-europe-africa class="w-6 h-6" />
                            </span>
                            <h3 class="text-lg font-semibold text-gray-900">Countries</h3>
                        </div>
                        <x-heroicon-o-eye class="w-5 h-5 text-gray-400 group-hover:text-indigo-600" />
                    </div>
                    <p class="text-sm text-gray-600 flex-1">Manage ISO country codes and legacy mappings.</p>
                    <span class="mt-4 inline-flex items-center text-sm font-medium text-indigo-600 group-hover:underline">Open &rarr;</span>
                </a>

                @php($lc = $entityColor('languages'))
                <a href="{{ route('languages.index') }}" class="group rounded-xl border border-gray-200 bg-white p-5 hover:shadow transition flex flex-col ring-1 ring-transparent hover:ring-{{ $lc['base'] }}/40">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <span class="p-2 rounded-md bg-fuchsia-50 text-fuchsia-600 group-hover:opacity-90">
                                <x-heroicon-o-language class="w-6 h-6" />
                            </span>
                            <h3 class="text-lg font-semibold text-gray-900">Languages</h3>
                        </div>
                        <x-heroicon-o-eye class="w-5 h-5 text-gray-400 group-hover:text-fuchsia-600" />
                    </div>
                    <p class="text-sm text-gray-600 flex-1">Maintain supported languages and default flag.</p>
                    <span class="mt-4 inline-flex items-center text-sm font-medium text-fuchsia-600 group-hover:underline">Open &rarr;</span>
                </a>
            </div>
        </section>
        @endauth

        {{-- Resources & Tools Group --}}
        <section class="space-y-4">
            <h2 class="text-sm font-semibold tracking-wide text-gray-500 uppercase">Resources &amp; Tools</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <a href="{{ url('/docs/api') }}" class="group rounded-xl border border-gray-200 bg-white p-5 hover:shadow transition flex flex-col">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <span class="p-2 rounded-md bg-indigo-50 text-indigo-600 group-hover:opacity-90">
                                <x-heroicon-o-book-open class="w-6 h-6" />
                            </span>
                            <h3 class="text-lg font-semibold text-gray-900">API Documentation</h3>
                        </div>
                        <x-heroicon-o-eye class="w-5 h-5 text-gray-400 group-hover:text-indigo-600" />
                    </div>
                    <p class="text-sm text-gray-600 flex-1">REST endpoints, schemas and integration notes.</p>
                    <span class="mt-4 inline-flex items-center text-sm font-medium text-indigo-600 group-hover:underline">Browse &rarr;</span>
                </a>

                @if(config('interface.show_spa_link'))
                <a href="{{ url('/cli') }}" class="group rounded-xl border border-gray-200 bg-white p-5 hover:shadow transition flex flex-col">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <span class="p-2 rounded-md bg-purple-50 text-purple-600 group-hover:opacity-90">
                                <x-heroicon-o-window class="w-6 h-6" />
                            </span>
                            <h3 class="text-lg font-semibold text-gray-900">SPA Client</h3>
                        </div>
                        <x-heroicon-o-eye class="w-5 h-5 text-gray-400 group-hover:text-purple-600" />
                    </div>
                    <p class="text-sm text-gray-600 flex-1">Vue.js client showcasing reactive workflows.</p>
                    <span class="mt-4 inline-flex items-center text-sm font-medium text-purple-600 group-hover:underline">Launch &rarr;</span>
                </a>
                @endif

                <a href="https://github.com/metanull/inventory-app" target="_blank" class="group rounded-xl border border-gray-200 bg-white p-5 hover:shadow transition flex flex-col">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <span class="p-2 rounded-md bg-gray-100 text-gray-800 group-hover:opacity-90">
                                <x-heroicon-o-code-bracket class="w-6 h-6" />
                            </span>
                            <h3 class="text-lg font-semibold text-gray-900">Source Code</h3>
                        </div>
                        <x-heroicon-o-eye class="w-5 h-5 text-gray-400 group-hover:text-gray-800" />
                    </div>
                    <p class="text-sm text-gray-600 flex-1">Explore repository & contributions.</p>
                    <span class="mt-4 inline-flex items-center text-sm font-medium text-gray-700 group-hover:underline">Open &rarr;</span>
                </a>

                <a href="https://metanull.github.io/inventory-app" target="_blank" class="group rounded-xl border border-gray-200 bg-white p-5 hover:shadow transition flex flex-col">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <span class="p-2 rounded-md bg-sky-50 text-sky-600 group-hover:opacity-90">
                                <x-heroicon-o-document-text class="w-6 h-6" />
                            </span>
                            <h3 class="text-lg font-semibold text-gray-900">Project Docs</h3>
                        </div>
                        <x-heroicon-o-eye class="w-5 h-5 text-gray-400 group-hover:text-sky-600" />
                    </div>
                    <p class="text-sm text-gray-600 flex-1">Development guidelines & architecture.</p>
                    <span class="mt-4 inline-flex items-center text-sm font-medium text-sky-600 group-hover:underline">Read &rarr;</span>
                </a>
            </div>
        </section>
    </div>
@endsection
