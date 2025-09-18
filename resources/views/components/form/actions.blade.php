@props([
    'cancelRoute' => '',
    'cancelLabel' => 'Cancel',
    'saveLabel' => 'Save',
    'entity' => null,
])

@php($c = $entityColor($entity))

<div class="px-4 py-4 sm:px-6 flex items-center justify-between bg-gray-50">
    <a href="{{ $cancelRoute }}" class="inline-flex items-center px-4 py-2 rounded-md border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 hover:text-gray-900 shadow-sm">
        {{ $cancelLabel }}
    </a>
    <button type="submit" class="inline-flex items-center px-4 py-2 rounded-md {{ $c['button'] ?? 'bg-indigo-600 hover:bg-indigo-700 text-white' }} text-sm font-medium shadow-sm">
        {{ $saveLabel }}
    </button>
</div>