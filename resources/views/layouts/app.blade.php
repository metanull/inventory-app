<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Favicon -->
        <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
        <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
        <link rel="apple-touch-icon" href="{{ asset('favicon.svg') }}">
        <link rel="manifest" href="{{ asset('site.webmanifest') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @if(!app()->environment('testing') && config('app.env') !== 'testing')
            @vite(['resources/css/app.css'])
        @else
            <!-- Testing environment: Skip Vite assets -->
            <style>
                body { 
                    font-family: 'Inter', system-ui, sans-serif; 
                    background-color: #f9fafb;
                    color: #111827;
                }
                .font-sans { font-family: 'Inter', system-ui, sans-serif; }
                .min-h-screen { min-height: 100vh; }
                .bg-gray-100 { background-color: #f3f4f6; }
                .bg-gray-50 { background-color: #f9fafb; }
                .antialiased { -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale; }
            </style>
        @endif

        <!-- Styles -->
        @livewireStyles
    </head>
    <body class="font-sans antialiased">
        <x-banner />

        <div class="min-h-screen bg-gray-50">
            @livewire('navigation-menu')

            <!-- Page Heading -->
            @if (isset($header))
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>

        @stack('modals')

        @livewireScripts
        
        <!-- Simple footer with version info -->
        @php
            $ver = is_callable($app_version_info) ? $app_version_info() : (array)($app_version_info ?? []);
            $appVer = $ver['version'] ?? config('app.version', env('APP_VERSION', 'dev'));
            $apiClientVer = $ver['api_client_version'] ?? null;
            $builtAt = $ver['build_timestamp'] ?? null;
        @endphp

        <footer class="w-full bg-white border-t border-gray-200 text-sm text-gray-600 py-2">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between">
                <div>
                    <span class="font-medium">{{ config('app.name') }}</span>
                    <span class="ml-2">v{{ $appVer }}</span>
                    @if($apiClientVer)
                        <span class="mx-2">•</span>
                        <span>api: v{{ $apiClientVer }}</span>
                    @endif
                </div>
                <div class="text-right text-xs">
                    @if($builtAt)
                        <span>Built: {{ $builtAt }}</span>
                    @endif
                </div>
            </div>
        </footer>
    </body>
</html>
