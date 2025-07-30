<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @if(!app()->environment('testing') && config('app.env') !== 'testing')
            @vite(['resources/css/app.css'])
        @else
            <!-- Testing environment: Skip Vite assets -->
            <style>
                body { font-family: 'Figtree', sans-serif; }
            </style>
        @endif

        <!-- Styles -->
        @livewireStyles
        
    </head>
    <body class="font-sans antialiased">
        <div class="bg-gray-50 text-black/50">
            <img id="background" class="absolute -left-20 top-0 max-w-[877px]" src="https://laravel.com/assets/img/welcome/background.svg" alt="Laravel background" />
            <div class="relative min-h-screen flex flex-col items-center justify-center selection:bg-[#FF2D20] selection:text-white">
                <div class="relative w-full max-w-2xl px-6 lg:max-w-7xl">
                    <header class="grid grid-cols-2 items-center gap-2 py-10 lg:grid-cols-3">
                        <div class="flex lg:justify-center lg:col-start-2">
                            <x-heroicon-o-home class="h-12 w-auto text-white lg:h-16 lg:text-[#FF2D20]" />
                        </div>
                        @if (Route::has('login'))
                            <nav class="-mx-3 flex flex-1 justify-end">
                                @auth
                                    <a
                                        href="{{ url('/web/dashboard') }}"
                                        class="rounded-md px-3 py-2 text-black ring-1 ring-transparent transition hover:text-black/70 focus:outline-none focus-visible:ring-[#FF2D20]"
                                    >
                                        Dashboard
                                    </a>

                                    <a
                                        href="{{ route('logout') }}"
                                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                                        class="rounded-md px-3 py-2 text-black ring-1 ring-transparent transition hover:text-black/70 focus:outline-none focus-visible:ring-[#FF2D20]"
                                    >
                                        Log out
                                    </a>
                                @else
                                    <a
                                        href="{{ route('login') }}"
                                        class="rounded-md px-3 py-2 text-black ring-1 ring-transparent transition hover:text-black/70 focus:outline-none focus-visible:ring-[#FF2D20]"
                                    >
                                        Log in
                                    </a>

                                    @if (Route::has('register'))
                                        <a
                                            href="{{ route('register') }}"
                                            class="rounded-md px-3 py-2 text-black ring-1 ring-transparent transition hover:text-black/70 focus:outline-none focus-visible:ring-[#FF2D20]"
                                        >
                                            Register
                                        </a>
                                    @endif
                                @endauth
                            </nav>
                        @endif
                    </header>

                    <main class="mt-6">
                        <div class="grid gap-6 lg:grid-cols-2 lg:gap-8">
                            <a
                                href="{{ url('/docs/api') }}"
                                class="flex items-start gap-4 rounded-lg bg-white p-6 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.08)] ring-1 ring-white/[0.05] transition duration-300 hover:text-black/70 hover:ring-black/20 focus:outline-none focus-visible:ring-[#FF2D20] lg:pb-10"
                            >
                                <div class="flex size-12 shrink-0 items-center justify-center rounded-full bg-[#FF2D20]/10 sm:size-16">
                                    <x-heroicon-o-document-text class="size-5 sm:size-6 text-[#FF2D20]" />
                                </div>
                                <div class="pt-3 sm:pt-5">
                                    <h2 class="text-xl font-semibold text-black">API Documentation</h2>
                                    <p class="mt-4 text-sm/relaxed">
                                        View the OpenAPI documentation for this application's REST API, generated by <span class="font-semibold">dedoc/Scramble</span>.
                                    </p>
                                </div>
                                <x-heroicon-o-arrow-right class="size-6 shrink-0 self-center stroke-[#FF2D20]" />
                            </a>

                            @guest
                                
                            <a
                                href="{{ route('login') }}"
                                class="flex items-start gap-4 rounded-lg bg-white p-6 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.08)] ring-1 ring-white/[0.05] transition duration-300 hover:text-black/70 hover:ring-black/20 focus:outline-none focus-visible:ring-[#FF2D20] lg:pb-10"
                            >
                                                            >
                                <div class="flex size-12 shrink-0 items-center justify-center rounded-full bg-[#FF2D20]/10 sm:size-16">
                                    <x-heroicon-o-user class="size-5 sm:size-6 text-[#FF2D20]" />
                                </div>
                                <div class="pt-3 sm:pt-5">
                                    <h2 class="text-xl font-semibold text-black">Anonymous</h2>
                                <x-heroicon-o-arrow-right class="size-6 shrink-0 self-center stroke-[#FF2D20]" />
                            </a>

                            @endguest

                            @auth

                            <a
                                href="{{ url('/web/dashboard') }}"
                                class="flex items-start gap-4 rounded-lg bg-white p-6 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.08)] ring-1 ring-white/[0.05] transition duration-300 hover:text-black/70 hover:ring-black/20 focus:outline-none focus-visible:ring-[#FF2D20] lg:pb-10"
                            >
                                <div class="flex size-12 shrink-0 items-center justify-center rounded-full bg-[#FF2D20]/10 sm:size-16">
                                    <x-heroicon-o-chart-bar class="size-5 sm:size-6 text-[#FF2D20]" />
                                </div>
                                <div class="pt-3 sm:pt-5">
                                    <h2 class="text-xl font-semibold text-black">Dashboard</h2>
                                    <p class="mt-4 text-sm/relaxed">
                                        The dashboard provides a central place to manage your account and view your inventory data. More features will be added soon—currently, it serves as a starting point after login.
                                    </p>
                                </div>
                                <x-heroicon-o-arrow-right class="size-6 shrink-0 self-center stroke-[#FF2D20]" />
                            </a>

                            @endauth
                            
                        
                            @guest
                                
                            <a
                                href="{{ route('login') }}"
                                class="flex items-start gap-4 rounded-lg bg-white p-6 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.08)] ring-1 ring-white/[0.05] transition duration-300 hover:text-black/70 hover:ring-black/20 focus:outline-none focus-visible:ring-[#FF2D20] lg:pb-10"
                            >
                                <div class="flex size-12 shrink-0 items-center justify-center rounded-full bg-[#FF2D20]/10 sm:size-16">
                                    <x-heroicon-o-key class="size-5 sm:size-6 text-[#FF2D20]" />
                                </div>
                                <div class="pt-3 sm:pt-5">
                                    <h2 class="text-xl font-semibold text-black">Authenticate</h2>
                                    <p class="mt-4 text-sm/relaxed">
                                        Log in to access your user dashboard and manage your account. Once authenticated, you can generate and manage your personal API tokens, which are required to securely access protected endpoints of the application.
                                    </p>
                                </div>
                                <x-heroicon-o-arrow-right class="size-6 shrink-0 self-center stroke-[#FF2D20]" />
                            </a>

                            <a
                                href="{{ route('register') }}"
                                class="flex items-start gap-4 rounded-lg bg-white p-6 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.08)] ring-1 ring-white/[0.05] transition duration-300 hover:text-black/70 hover:ring-black/20 focus:outline-none focus-visible:ring-[#FF2D20] lg:pb-10"
                            >
                                <div class="flex size-12 shrink-0 items-center justify-center rounded-full bg-[#FF2D20]/10 sm:size-16">
                                    <x-heroicon-o-user-plus class="size-5 sm:size-6 text-[#FF2D20]" />
                                </div>
                                <div class="pt-3 sm:pt-5">
                                    <h2 class="text-xl font-semibold text-black">Register</h2>
                                    <p class="mt-4 text-sm/relaxed">
                                        Create a new account to access your user dashboard and manage your inventory. After registering, you can log in to generate and manage your personal API tokens, which are required to securely access protected endpoints of the application.
                                    </p>
                                </div>
                                <x-heroicon-o-arrow-right class="size-6 shrink-0 self-center stroke-[#FF2D20]" />
                            </a>

                            @endguest

                            @auth

                            <a
                                href="{{ route('api-tokens.index') }}"
                                class="flex items-start gap-4 rounded-lg bg-white p-6 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.08)] ring-1 ring-white/[0.05] transition duration-300 hover:text-black/70 hover:ring-black/20 focus:outline-none focus-visible:ring-[#FF2D20] lg:pb-10"
                            >
                                <div class="flex size-12 shrink-0 items-center justify-center rounded-full bg-[#FF2D20]/10 sm:size-16">
                                    <x-heroicon-o-key class="size-5 sm:size-6 text-[#FF2D20]" />
                                </div>
                                <div class="pt-3 sm:pt-5">
                                    <h2 class="text-xl font-semibold text-black">API Tokens</h2>
                                    <p class="mt-4 text-sm/relaxed">
                                        Manage your API tokens to securely access protected endpoints of the application. You can create, view, and delete tokens as needed. Each token is associated with your user account and can be used to authenticate API requests.
                                    </p>
                                </div>
                                <x-heroicon-o-arrow-right class="size-6 shrink-0 self-center stroke-[#FF2D20]" />
                            </a>

                            <a
                                href="{{ route('profile.show') }}"
                                class="flex items-start gap-4 rounded-lg bg-white p-6 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.08)] ring-1 ring-white/[0.05] transition duration-300 hover:text-black/70 hover:ring-black/20 focus:outline-none focus-visible:ring-[#FF2D20] lg:pb-10"
                            >
                                <div class="flex size-12 shrink-0 items-center justify-center rounded-full bg-[#FF2D20]/10 sm:size-16">
                                    <x-heroicon-o-user-circle class="size-5 sm:size-6 text-[#FF2D20]" />
                                </div>
                                <div class="pt-3 sm:pt-5">
                                    <h2 class="text-xl font-semibold text-black">User Profile</h2>
                                    <p class="mt-4 text-sm/relaxed">
                                        View and manage your user profile information, including your name, email address, and other personal details. You can also update your password and other account settings from this page.
                                    </p>
                                </div>
                                <x-heroicon-o-arrow-right class="size-6 shrink-0 self-center stroke-[#FF2D20]" />
                            </a>

                            @endauth
                            
                        </div>
                    </main>

                    <footer class="py-16 text-center text-sm text-black">
                        Laravel v{{ Illuminate\Foundation\Application::VERSION }} (PHP v{{ PHP_VERSION }}) (Environment: {{ app()->environment() }})
                    </footer>
                </div>
            </div>
        </div>


        <div class="hidden">
            <form id="logout-form" method="POST" action="{{ route('logout') }}">
                @csrf
            </form>
        </div>

    </body>
</html>
