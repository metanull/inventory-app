{{--
    Sidebar Card Component
    Wrapper for sidebar sections with optional title and icon
--}}

@props([
    'title' => '',
    'icon' => null,
    'compact' => true,
])

<div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
    @if($title)
        <div class="px-4 py-3 border-b border-gray-200 bg-gray-50">
            <h3 class="text-sm font-semibold text-gray-900 flex items-center gap-2">
                @if($icon)
                    <x-dynamic-component :component="'heroicon-o-' . $icon" class="w-4 h-4 text-gray-500" />
                @endif
                {{ $title }}
            </h3>
        </div>
    @endif
    <div class="px-4 py-3 {{ $compact ? 'space-y-2' : 'space-y-3' }}">
        {{ $slot }}
    </div>
</div>
