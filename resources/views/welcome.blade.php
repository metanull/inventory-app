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

    {{-- Features Section --}}
    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8">
                
                {{-- Inventory Management --}}
                <x-welcome-feature-card>
                    <x-slot name="icon">
                        <x-heroicon-o-archive-box class="size-6 stroke-blue-800" />
                    </x-slot>
                    <x-slot name="title">Inventory Management</x-slot>
                    <x-slot name="description">
                        Comprehensive digital cataloging system for museum collections. Track, organize, and manage your artifacts with detailed metadata and high-resolution imagery.
                    </x-slot>
                    <x-slot name="linkUrl">/cli</x-slot>
                    <x-slot name="linkText">Inventory management client</x-slot>
                </x-welcome-feature-card>
                
                {{-- API Documentation --}}
                <x-welcome-feature-card>
                    <x-slot name="icon">
                        <x-heroicon-o-cursor-arrow-rays class="size-6 stroke-blue-800" />
                    </x-slot>
                    <x-slot name="title">API Documentation</x-slot>
                    <x-slot name="description">
                        Powerful REST API for developers. Integrate museum data into your applications with comprehensive documentation and examples.
                    </x-slot>
                    <x-slot name="linkUrl">/docs/api</x-slot>
                    <x-slot name="linkText">View API docs</x-slot>
                </x-welcome-feature-card>
                
                {{-- Source Code on GitHub --}}
                <x-welcome-feature-card>
                    <x-slot name="icon">
                        <x-heroicon-o-code-bracket class="size-6 stroke-blue-800" />
                    </x-slot>
                    <x-slot name="title">Source Code</x-slot>
                    <x-slot name="description">
                        Explore the full source code, contribute, or report issues directly on GitHub. Join the community and help improve the project.
                    </x-slot>
                    <x-slot name="linkUrl">https://github.com/metanull/inventory-app</x-slot>
                    <x-slot name="linkText">View on GitHub</x-slot>
                </x-welcome-feature-card>
                
                {{-- Documentation on GitHub page --}}
                <x-welcome-feature-card>
                    <x-slot name="icon">
                        <x-heroicon-o-document-text class="size-6 stroke-blue-800" />
                    </x-slot>
                    <x-slot name="title">GitHub</x-slot>
                    <x-slot name="description">
                        Access the application's documentation and source code on GitHub. Contribute, report issues, or explore technical details about the project.
                    </x-slot>
                    <x-slot name="linkUrl">https://metanull.github.io/inventory-app</x-slot>
                    <x-slot name="linkText">GitHub pages</x-slot>
                </x-welcome-feature-card>
                
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
