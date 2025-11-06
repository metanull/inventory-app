{{--
    Navigation Sidebar Card
    Displays back link and navigation options
--}}

@props([
    'backRoute' => null,
    'backLabel' => 'Back to list',
])

<x-sidebar.card title="Navigation" icon="arrows-pointing-out">
    <div class="space-y-2 text-sm">
        @if($backRoute)
            <a href="{{ $backRoute }}" class="flex items-center gap-2 text-blue-600 hover:text-blue-800 font-medium transition">
                <x-heroicon-o-arrow-left class="w-4 h-4" />
                {{ $backLabel }}
            </a>
        @endif
        {{ $slot }}
    </div>
</x-sidebar.card>
