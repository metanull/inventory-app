<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @if(!app()->environment('testing') && config('app.env') !== 'testing')
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @else
            <!-- Testing environment: Skip Vite assets -->
            <style>
                body { 
                    font-family: 'Inter', system-ui, sans-serif; 
                    background-color: #f9fafb;
                    color: #111827;
                }
                .font-sans { font-family: 'Inter', system-ui, sans-serif; }
            </style>
        @endif

        <!-- Styles -->
        @livewireStyles
    </head>
    <body>
        <div class="font-sans text-gray-900 antialiased">
            {{ $slot }}
        </div>

        @livewireScripts
    </body>
</html>
