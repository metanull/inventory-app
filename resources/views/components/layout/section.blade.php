@props([
    'title',
    'icon' => null,
])

@php
    // Icon component mapping
    $iconComponents = [
        'photo' => 'heroicon-o-photo',
        'language' => 'heroicon-o-language',
        'document-text' => 'heroicon-o-document-text',
        'users' => 'heroicon-o-users',
        'building-library' => 'heroicon-o-building-library',
        'folder' => 'heroicon-o-folder',
        'globe-alt' => 'heroicon-o-globe-alt',
        'link' => 'heroicon-o-link',
    ];

    $iconComponent = $icon && isset($iconComponents[$icon]) ? $iconComponents[$icon] : null;
@endphp

<div {{ $attributes->merge(['class' => 'bg-white shadow-sm rounded-lg']) }}>
    <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                @if($iconComponent)
                    <x-dynamic-component :component="$iconComponent" class="w-5 h-5 text-gray-600" />
                @endif
                <h2 class="text-xl font-semibold text-gray-900">{{ $title }}</h2>
            </div>
            @if(isset($action))
                {{ $action }}
            @endif
        </div>
    </div>
    <div class="p-6">
        {{ $slot }}
    </div>
</div>
