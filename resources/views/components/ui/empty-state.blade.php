@props([
    'icon' => 'document-text',
    'title' => 'No items',
    'message' => null,
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
    ];

    $iconComponent = isset($iconComponents[$icon]) ? $iconComponents[$icon] : 'heroicon-o-document-text';
@endphp

<div {{ $attributes->merge(['class' => 'text-center py-12 bg-gray-50 rounded-lg']) }}>
    <x-dynamic-component :component="$iconComponent" class="mx-auto h-12 w-12 text-gray-400" />
    <h3 class="mt-2 text-sm font-medium text-gray-900">{{ $title }}</h3>
    @if($message)
        <p class="mt-1 text-sm text-gray-500">{{ $message }}</p>
    @endif
    @if($slot->isNotEmpty())
        <div class="mt-6">
            {{ $slot }}
        </div>
    @endif
</div>
