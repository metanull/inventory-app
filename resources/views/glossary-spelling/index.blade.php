@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <div class="mb-6">
        <a href="{{ route('glossaries.show', $glossary) }}" class="text-sm text-emerald-600 hover:text-emerald-800">&larr; Back to glossary</a>
    </div>

    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-semibold text-gray-800 flex items-center gap-2">
            <span class="inline-flex items-center justify-center p-2 rounded-md bg-gray-600 text-white">
                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 0 1 .865-.501 48.172 48.172 0 0 0 3.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z" />
                </svg>
            </span>
            <span class="text-gray-700">Spellings for: {{ $glossary->internal_name }}</span>
        </h1>
        <a href="{{ route('glossaries.spellings.create', $glossary) }}" class="inline-flex items-center px-3 py-2 rounded-md bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium">
            <svg class="w-5 h-5 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Add Spelling
        </a>
    </div>

    @if(session('success'))
        <x-ui.alert :message="session('success')" type="success" entity="spelling" />
    @endif

    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        @if($spellings->isEmpty())
            <div class="px-6 py-12 text-center text-gray-500">
                <p>No spellings found for this glossary entry.</p>
            </div>
        @else
            <ul class="divide-y divide-gray-200">
                @foreach($spellings as $spelling)
                    <li class="px-6 py-4 hover:bg-gray-50">
                        <a href="{{ route('glossaries.spellings.show', [$glossary, $spelling]) }}" class="block">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900">{{ $spelling->language->internal_name }}</p>
                                    <p class="mt-1 text-sm text-gray-600 font-mono">{{ $spelling->spelling }}</p>
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
