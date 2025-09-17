@props([
    'groupClass' => 'space-y-1',
])
<nav x-data="{ mobile:false, openMenu:null }" class="bg-white border-b border-gray-100 shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
        <div class="flex items-center gap-8">
            <a href="{{ route('web.welcome') }}" class="flex items-center gap-2 text-indigo-600 font-semibold">
                <x-application-mark class="block h-8 w-auto" />
                <span class="hidden sm:inline">{{ config('app.name') }}</span>
            </a>
            <div class="hidden md:flex gap-6 text-sm">
                <!-- Inventory Dropdown -->
                <div class="relative" @mouseenter="openMenu='inventory'" @mouseleave="openMenu=null">
                    <button @click="openMenu = openMenu==='inventory'? null : 'inventory'" type="button" class="inline-flex items-center gap-1 px-2 py-1 rounded-md font-medium {{ request()->routeIs('items.*') || request()->routeIs('partners.*') ? 'text-indigo-700 bg-indigo-50' : 'text-gray-600 hover:text-gray-800 hover:bg-gray-50' }}">
                        <x-heroicon-o-squares-2x2 class="w-4 h-4" />
                        Inventory
                        <span class="w-4 h-4 transition" x-bind:class="openMenu==='inventory' ? 'rotate-180' : ''">
                            <x-heroicon-o-chevron-down class="w-4 h-4" />
                        </span>
                    </button>
                    <div x-show="openMenu==='inventory'" x-transition @click.outside="openMenu=null" class="absolute z-30 mt-2 w-52 rounded-md border border-gray-200 bg-white shadow-lg py-2">
                        <a href="{{ route('items.index') }}" class="flex items-center gap-2 px-3 py-2 text-sm {{ request()->routeIs('items.*') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-700 hover:bg-gray-50' }}">
                            <x-heroicon-o-archive-box class="w-4 h-4" /> Items
                        </a>
                        <a href="{{ route('partners.index') }}" class="flex items-center gap-2 px-3 py-2 text-sm {{ request()->routeIs('partners.*') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-700 hover:bg-gray-50' }}">
                            <x-heroicon-o-user-group class="w-4 h-4" /> Partners
                        </a>
                    </div>
                </div>

                <!-- Reference Dropdown -->
                <div class="relative" @mouseenter="openMenu='reference'" @mouseleave="openMenu=null">
                    <button @click="openMenu = openMenu==='reference'? null : 'reference'" type="button" class="inline-flex items-center gap-1 px-2 py-1 rounded-md font-medium {{ request()->routeIs('countries.*') || request()->routeIs('languages.*') ? 'text-indigo-700 bg-indigo-50' : 'text-gray-600 hover:text-gray-800 hover:bg-gray-50' }}">
                        <x-heroicon-o-book-open class="w-4 h-4" /> Reference
                        <span class="w-4 h-4 transition" x-bind:class="openMenu==='reference' ? 'rotate-180' : ''">
                            <x-heroicon-o-chevron-down class="w-4 h-4" />
                        </span>
                    </button>
                    <div x-show="openMenu==='reference'" x-transition @click.outside="openMenu=null" class="absolute z-30 mt-2 w-56 rounded-md border border-gray-200 bg-white shadow-lg py-2">
                        @php($cc = $entityColor('countries'))
                        @php($lc = $entityColor('languages'))
                        <a href="{{ route('countries.index') }}" class="flex items-center gap-2 px-3 py-2 text-sm {{ request()->routeIs('countries.*') ? $cc['badge'].' font-medium' : 'text-gray-700 hover:bg-gray-50' }}">
                            <x-heroicon-o-globe-europe-africa class="w-4 h-4" /> Countries
                        </a>
                        <a href="{{ route('languages.index') }}" class="flex items-center gap-2 px-3 py-2 text-sm {{ request()->routeIs('languages.*') ? $lc['badge'].' font-medium' : 'text-gray-700 hover:bg-gray-50' }}">
                            <x-heroicon-o-language class="w-4 h-4" /> Languages
                        </a>
                    </div>
                </div>

                <!-- Resources Dropdown -->
                <div class="relative" @mouseenter="openMenu='resources'" @mouseleave="openMenu=null">
                    <button @click="openMenu = openMenu==='resources'? null : 'resources'" type="button" class="inline-flex items-center gap-1 px-2 py-1 rounded-md font-medium {{ request()->is('cli*') ? 'text-indigo-700 bg-indigo-50' : 'text-gray-600 hover:text-gray-800 hover:bg-gray-50' }}">
                        <x-heroicon-o-circle-stack class="w-4 h-4" /> Resources
                        <span class="w-4 h-4 transition" x-bind:class="openMenu==='resources' ? 'rotate-180' : ''">
                            <x-heroicon-o-chevron-down class="w-4 h-4" />
                        </span>
                    </button>
                    <div x-show="openMenu==='resources'" x-transition @click.outside="openMenu=null" class="absolute z-30 mt-2 w-60 rounded-md border border-gray-200 bg-white shadow-lg py-2">
                        @if(config('interface.show_spa_link'))
                        <a href="{{ url('/cli') }}" class="flex items-center gap-2 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50">
                            <x-heroicon-o-window class="w-4 h-4" /> SPA Client
                        </a>
                        @endif
                        <a href="{{ url('/docs/api') }}" class="flex items-center gap-2 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50">
                            <x-heroicon-o-book-open class="w-4 h-4" /> API Docs
                        </a>
                        <a href="https://github.com/metanull/inventory-app" target="_blank" class="flex items-center gap-2 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50">
                            <x-heroicon-o-code-bracket class="w-4 h-4" /> Source Code
                        </a>
                        <a href="https://metanull.github.io/inventory-app" target="_blank" class="flex items-center gap-2 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50">
                            <x-heroicon-o-document-text class="w-4 h-4" /> Project Docs
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-4">
            @auth
                <div class="hidden md:flex items-center gap-3">
                    <span class="text-sm text-gray-600">{{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-xs px-2 py-1 rounded border border-gray-300 text-gray-600 hover:bg-gray-50">Logout</button>
                    </form>
                </div>
            @else
                <div class="hidden md:flex items-center gap-2 text-sm">
                    <a href="{{ route('login') }}" class="px-2 py-1 rounded text-gray-600 hover:text-gray-800 hover:bg-gray-50">Login</a>
                    @if(Route::has('register'))
                        <a href="{{ route('register') }}" class="px-2 py-1 rounded text-gray-600 hover:text-gray-800 hover:bg-gray-50">Register</a>
                    @endif
                </div>
            @endauth
            <button @click="mobile = !mobile" class="md:hidden inline-flex items-center justify-center p-2 rounded-md text-gray-500 hover:bg-gray-100 focus:outline-none">
                <x-heroicon-o-bars-3 class="w-6 h-6" x-show="!mobile" />
                <x-heroicon-o-x-mark class="w-6 h-6" x-show="mobile" />
            </button>
        </div>
    </div>
    <div x-show="mobile" x-transition class="md:hidden border-t border-gray-200 bg-white">
        <div class="px-4 py-4 space-y-6 text-sm">
            <div class="space-y-2">
                <p class="text-[11px] font-semibold text-gray-400 uppercase">Inventory</p>
                <a href="{{ route('items.index') }}" class="block px-2 py-1 rounded {{ request()->routeIs('items.*') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-800' }}">Items</a>
                <a href="{{ route('partners.index') }}" class="block px-2 py-1 rounded {{ request()->routeIs('partners.*') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-800' }}">Partners</a>
            </div>
            <div class="space-y-2">
                <p class="text-[11px] font-semibold text-gray-400 uppercase">Reference</p>
                @php($cc = $entityColor('countries'))
                @php($lc = $entityColor('languages'))
                <a href="{{ route('countries.index') }}" class="block px-2 py-1 rounded {{ request()->routeIs('countries.*') ? $cc['badge'].' font-medium' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-800' }}">Countries</a>
                <a href="{{ route('languages.index') }}" class="block px-2 py-1 rounded {{ request()->routeIs('languages.*') ? $lc['badge'].' font-medium' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-800' }}">Languages</a>
            </div>
            <div class="space-y-2">
                <p class="text-[11px] font-semibold text-gray-400 uppercase">Resources</p>
                @if(config('interface.show_spa_link'))
                <a href="{{ url('/cli') }}" class="block px-2 py-1 rounded text-gray-600 hover:bg-gray-50 hover:text-gray-800">SPA Client</a>
                @endif
                <a href="{{ url('/docs/api') }}" class="block px-2 py-1 rounded text-gray-600 hover:bg-gray-50 hover:text-gray-800">API Docs</a>
                <a href="https://github.com/metanull/inventory-app" target="_blank" class="block px-2 py-1 rounded text-gray-600 hover:bg-gray-50 hover:text-gray-800">Source Code</a>
                <a href="https://metanull.github.io/inventory-app" target="_blank" class="block px-2 py-1 rounded text-gray-600 hover:bg-gray-50 hover:text-gray-800">Project Docs</a>
            </div>
            <div class="pt-2 border-t border-gray-100">
                @auth
                    <div class="flex items-center justify-between">
                        <span class="text-gray-600">{{ auth()->user()->name }}</span>
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="text-xs px-2 py-1 rounded border border-gray-300 text-gray-600 hover:bg-gray-50">Logout</button>
                        </form>
                    </div>
                @else
                    <div class="flex gap-2">
                        <a href="{{ route('login') }}" class="flex-1 text-center px-2 py-1 rounded border border-gray-300 text-gray-600 hover:bg-gray-50">Login</a>
                        @if(Route::has('register'))
                            <a href="{{ route('register') }}" class="flex-1 text-center px-2 py-1 rounded border border-gray-300 text-gray-600 hover:bg-gray-50">Register</a>
                        @endif
                    </div>
                @endauth
            </div>
        </div>
    </div>
</nav>
