{{-- Welcome Page --}}
<x-app-layout>
    {{-- Hero Section --}}
    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-welcome-hero>
                <x-slot name="title">
                    {{ config('app.name') }}
                </x-slot>
                
                <x-slot name="description">
                    Comprehensive digital cataloging system for museum collections. Track, organize, and manage your artifacts with detailed metadata and high-resolution imagery.
                </x-slot>
                
                <x-slot name="actions">
                    @auth
                        <x-welcome-cta-button href="{{ url('/cli') }}" variant="outline">
                            <x-heroicon-o-squares-2x2 class="size-5 mr-2" />
                            Inventory Management Client
                        </x-welcome-cta-button>
                    @else
                        <x-welcome-cta-button href="{{ route('login') }}" variant="outline">
                            <x-heroicon-o-arrow-right-on-rectangle class="size-5 mr-2" />
                            Login
                        </x-welcome-cta-button>
                        
                        @if (Route::has('register'))
                            <x-welcome-cta-button href="{{ route('register') }}" variant="secondary">
                                <x-heroicon-o-user-plus class="size-5 mr-2" />
                                Register
                            </x-welcome-cta-button>
                        @endif
                    @endauth
                </x-slot>
                
                <x-slot name="illustration">
                    <x-heroicon-o-building-library class="size-32 text-blue-200 opacity-50" />
                </x-slot>
            </x-welcome-hero>
        </div>
    </div>

    {{-- Primary Access Tiles (Guest emphasis) --}}
    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8">
                @guest
                    {{-- Login Tile (Prominent) --}}
                    <a href="{{ route('login') }}" class="group rounded-xl border border-indigo-300 bg-white p-6 hover:shadow transition flex flex-col ring-1 ring-indigo-100 hover:ring-indigo-300">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-3">
                                <span class="p-2 rounded-md bg-indigo-50 text-indigo-600 group-hover:bg-indigo-100">
                                    <x-heroicon-o-arrow-right-on-rectangle class="w-6 h-6" />
                                </span>
                                <h2 class="text-lg font-semibold text-gray-900">Sign In</h2>
                            </div>
                            <x-heroicon-o-eye class="w-5 h-5 text-gray-400 group-hover:text-indigo-500" />
                        </div>
                        <p class="text-sm text-gray-600 flex-1">Access the authenticated inventory management portal.</p>
                        <span class="mt-4 inline-flex items-center text-sm font-medium text-indigo-600 group-hover:text-indigo-700">Login <span class="ml-1">&rarr;</span></span>
                    </a>
                    @if(Route::has('register'))
                        {{-- Register Tile --}}
                        <a href="{{ route('register') }}" class="group rounded-xl border border-gray-200 bg-white p-6 hover:border-indigo-300 hover:shadow transition flex flex-col">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center gap-3">
                                    <span class="p-2 rounded-md bg-indigo-50 text-indigo-600 group-hover:bg-indigo-100">
                                        <x-heroicon-o-user-plus class="w-6 h-6" />
                                    </span>
                                    <h2 class="text-lg font-semibold text-gray-900">Create Account</h2>
                                </div>
                                <x-heroicon-o-eye class="w-5 h-5 text-gray-400 group-hover:text-indigo-500" />
                            </div>
                            <p class="text-sm text-gray-600 flex-1">Register to start cataloging items and managing partners.</p>
                            <span class="mt-4 inline-flex items-center text-sm font-medium text-indigo-600 group-hover:text-indigo-700">Register <span class="ml-1">&rarr;</span></span>
                        </a>
                    @endif
                @else
                    {{-- Direct Inventory Access (Authenticated) --}}
                    <a href="{{ route('items.index') }}" class="group rounded-xl border border-teal-300 bg-white p-6 hover:shadow transition flex flex-col ring-1 ring-teal-100 hover:ring-teal-300">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-3">
                                <span class="p-2 rounded-md bg-teal-50 text-teal-600 group-hover:bg-teal-100">
                                    <x-heroicon-o-archive-box class="w-6 h-6" />
                                </span>
                                <h2 class="text-lg font-semibold text-gray-900">Items</h2>
                            </div>
                            <x-heroicon-o-eye class="w-5 h-5 text-gray-400 group-hover:text-indigo-500" />
                        </div>
                        <p class="text-sm text-gray-600 flex-1">Browse and manage inventory item records.</p>
                        <span class="mt-4 inline-flex items-center text-sm font-medium text-teal-600 group-hover:text-teal-700">Open <span class="ml-1">&rarr;</span></span>
                    </a>
                @endguest

                {{-- SPA Client --}}
                <a href="{{ url('/cli') }}" class="group rounded-xl border border-gray-200 bg-white p-6 hover:border-indigo-300 hover:shadow transition flex flex-col">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <span class="p-2 rounded-md bg-indigo-50 text-indigo-600 group-hover:bg-indigo-100">
                                <x-heroicon-o-window class="w-6 h-6" />
                            </span>
                            <h2 class="text-lg font-semibold text-gray-900">SPA Client</h2>
                        </div>
                        <x-heroicon-o-eye class="w-5 h-5 text-gray-400 group-hover:text-indigo-500" />
                    </div>
                    <p class="text-sm text-gray-600 flex-1">Vue.js single-page application sharing the same API backend.</p>
                    <span class="mt-4 inline-flex items-center text-sm font-medium text-indigo-600 group-hover:text-indigo-700">Launch <span class="ml-1">&rarr;</span></span>
                </a>

                {{-- API Documentation --}}
                <a href="{{ url('/docs/api') }}" class="group rounded-xl border border-gray-200 bg-white p-6 hover:border-indigo-300 hover:shadow transition flex flex-col">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <span class="p-2 rounded-md bg-indigo-50 text-indigo-600 group-hover:bg-indigo-100">
                                <x-heroicon-o-book-open class="w-6 h-6" />
                            </span>
                            <h2 class="text-lg font-semibold text-gray-900">API Docs</h2>
                        </div>
                        <x-heroicon-o-eye class="w-5 h-5 text-gray-400 group-hover:text-indigo-500" />
                    </div>
                    <p class="text-sm text-gray-600 flex-1">Reference for the REST endpoints and data contracts.</p>
                    <span class="mt-4 inline-flex items-center text-sm font-medium text-indigo-600 group-hover:text-indigo-700">Browse <span class="ml-1">&rarr;</span></span>
                </a>

                {{-- Source Code --}}
                <a href="https://github.com/metanull/inventory-app" target="_blank" class="group rounded-xl border border-gray-200 bg-white p-6 hover:border-indigo-300 hover:shadow transition flex flex-col">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <span class="p-2 rounded-md bg-indigo-50 text-indigo-600 group-hover:bg-indigo-100">
                                <x-heroicon-o-code-bracket class="w-6 h-6" />
                            </span>
                            <h2 class="text-lg font-semibold text-gray-900">Source Code</h2>
                        </div>
                        <x-heroicon-o-eye class="w-5 h-5 text-gray-400 group-hover:text-indigo-500" />
                    </div>
                    <p class="text-sm text-gray-600 flex-1">Explore the repository and contribute improvements.</p>
                    <span class="mt-4 inline-flex items-center text-sm font-medium text-indigo-600 group-hover:text-indigo-700">Open <span class="ml-1">&rarr;</span></span>
                </a>

                {{-- Project Docs --}}
                <a href="https://metanull.github.io/inventory-app" target="_blank" class="group rounded-xl border border-gray-200 bg-white p-6 hover:border-indigo-300 hover:shadow transition flex flex-col">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <span class="p-2 rounded-md bg-indigo-50 text-indigo-600 group-hover:bg-indigo-100">
                                <x-heroicon-o-document-text class="w-6 h-6" />
                            </span>
                            <h2 class="text-lg font-semibold text-gray-900">Project Docs</h2>
                        </div>
                        <x-heroicon-o-eye class="w-5 h-5 text-gray-400 group-hover:text-indigo-500" />
                    </div>
                    <p class="text-sm text-gray-600 flex-1">Architecture notes, developer guides & operational procedures.</p>
                    <span class="mt-4 inline-flex items-center text-sm font-medium text-indigo-600 group-hover:text-indigo-700">Read <span class="ml-1">&rarr;</span></span>
                </a>
            </div>
        </div>
    </div>

    {{-- Call to Action Section --}}
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-gray-50 rounded-xl p-8 lg:p-12 text-center">
                <x-heroicon-o-sparkles class="size-16 mx-auto text-blue-800 mb-6" />
                <h2 class="text-3xl font-bold text-gray-900 mb-4">
                    Ready to Manager your Items?
                </h2>
                <p class="text-lg text-gray-600 mb-8 max-w-2xl mx-auto">
                    Clicking the link below will allow you to manage your inventory and access all cataloging features.
                </p>
                <div class="flex flex-wrap justify-center gap-4">
                    @guest
                        <x-welcome-cta-button href="{{ route('register') }}" variant="primary">
                            <x-heroicon-o-user-plus class="size-5 mr-2" />
                            Get Started Today
                        </x-welcome-cta-button>
                        <x-welcome-cta-button href="{{ route('login') }}" variant="secondary">
                            <x-heroicon-o-arrow-right-on-rectangle class="size-5 mr-2" />
                            Sign In
                        </x-welcome-cta-button>
                    @else
                        <x-welcome-cta-button href="{{ url('/cli') }}" variant="primary">
                            <x-heroicon-o-squares-2x2 class="size-5 mr-2" />
                            Continue to the Inventory Management Client
                        </x-welcome-cta-button>
                    @endguest
                </div>
            </div>
        </div>
    </div>

    {{-- Hidden logout form for authenticated users --}}
    @auth
        <form id="logout-form" method="POST" action="{{ route('logout') }}" class="hidden">
            @csrf
        </form>
    @endauth
</x-app-layout>
