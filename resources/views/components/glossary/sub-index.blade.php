@props([
    'glossary',         // The parent glossary object
    'items',            // Collection of sub-items (translations or spellings)
    'itemType',         // 'translation' or 'spelling'
    'itemDisplayField', // Field to display as primary content
    'createRoute',      // Route to create new item
    'showRoutePattern', // Route pattern with placeholders for showing individual items
    'backRoute',        // Route to go back to glossary
])

@php
    $title = ucfirst($itemType) . 's for: ' . $glossary->internal_name;
    $addButtonText = 'Add ' . ucfirst($itemType);
    $emptyMessage = 'No ' . $itemType . 's found for this glossary entry.';
    $iconSvg = $itemType === 'translation' 
        ? 'm10.5 21 5.25-11.25L21 21m-9-3h7.5M3 5.621a48.474 48.474 0 0 1 6-.371m0 0c1.12 0 2.233.038 3.334.114M9 5.25V3m3.334 2.364C11.176 10.658 7.69 15.08 3 17.502m9.334-12.138c.896.061 1.785.147 2.666.257m-4.589 8.495a18.023 18.023 0 0 1-3.827-5.802'
        : 'M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 0 1 .865-.501 48.172 48.172 0 0 0 3.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z';
@endphp

@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <div class="mb-6">
        <a href="{{ $backRoute }}" class="text-sm text-emerald-600 hover:text-emerald-800">&larr; Back to glossary</a>
    </div>

    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-semibold text-gray-800 flex items-center gap-2">
            <span class="inline-flex items-center justify-center p-2 rounded-md bg-gray-600 text-white">
                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $iconSvg }}" />
                </svg>
            </span>
            <span class="text-gray-700">{{ $title }}</span>
        </h1>
        <a href="{{ $createRoute }}" class="inline-flex items-center px-3 py-2 rounded-md bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium">
            <svg class="w-5 h-5 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            {{ $addButtonText }}
        </a>
    </div>

    @if(session('success'))
        <x-ui.alert :message="session('success')" type="success" :entity="$itemType" />
    @endif

    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        @if($items->isEmpty())
            <div class="px-6 py-12 text-center text-gray-500">
                <p>{{ $emptyMessage }}</p>
            </div>
        @else
            <ul class="divide-y divide-gray-200">
                @foreach($items as $item)
                    @php
                        // Generate the show route for this specific item
                        $showRoute = str_replace(['{glossary}', '{item}'], [$glossary->id, $item->id], $showRoutePattern);
                    @endphp
                    <li class="px-6 py-4 hover:bg-gray-50">
                        <a href="{{ $showRoute }}" class="block">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900">{{ $item->language->internal_name }}</p>
                                    <p class="mt-1 text-sm text-gray-600 {{ $itemType === 'spelling' ? 'font-mono' : '' }}">
                                        {{ $itemType === 'spelling' ? $item->$itemDisplayField : Str::limit($item->$itemDisplayField, 100) }}
                                    </p>
                                </div>
                                <div class="ml-4">
                                    <svg class="w-5 h-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                                    </svg>
                                </div>
                            </div>
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>
@endsection
