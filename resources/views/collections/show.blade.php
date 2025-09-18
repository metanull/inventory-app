@extends('layouts.app')

@section('content')
@php($c = $entityColor('collections'))
<div class="max-w-5xl mx-auto py-8 px-4 sm:px-6 lg:px-8 space-y-6">
    <x-entity.header entity="collections" :title="$collection->internal_name">
        <div class="flex items-center gap-2">
            <a href="{{ route('collections.edit', $collection) }}" class="inline-flex items-center px-3 py-1.5 rounded-md {{ $c['button'] }} text-sm font-medium">
                <x-heroicon-o-pencil-square class="w-5 h-5 mr-1" /> Edit
            </a>
            <form action="{{ route('collections.destroy', $collection) }}" method="POST" onsubmit="return confirm('Delete this collection?')">
                @csrf @method('DELETE')
                <button type="submit" class="inline-flex items-center px-3 py-1.5 rounded-md bg-red-600 hover:bg-red-700 text-white text-sm font-medium">
                    <x-heroicon-o-trash class="w-5 h-5 mr-1" /> Delete
                </button>
            </form>
            @if($collection->backward_compatibility)
                <span class="px-2 py-0.5 text-xs rounded {{ $c['badge'] }}">Legacy: {{ $collection->backward_compatibility }}</span>
            @endif
        </div>
    </x-entity.header>

    <div class="bg-white shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg divide-y divide-gray-200">
        <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
            <h2 class="text-lg font-medium text-gray-900">Information</h2>
        </div>
        <dl>
            <div class="px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 bg-gray-50">
                <dt class="text-sm font-medium text-gray-700">Internal Name</dt>
                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $collection->internal_name }}</dd>
            </div>
            <div class="px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                <dt class="text-sm font-medium text-gray-700">Language</dt>
                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $collection->language->internal_name ?? $collection->language_id }}</dd>
            </div>
            <div class="px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 bg-gray-50">
                <dt class="text-sm font-medium text-gray-700">Context</dt>
                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $collection->context->internal_name ?? '—' }}</dd>
            </div>
            <div class="px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                <dt class="text-sm font-medium text-gray-700">Legacy ID</dt>
                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $collection->backward_compatibility ?? '—' }}</dd>
            </div>
        </dl>
    </div>
</div>
@endsection
