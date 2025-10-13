@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    @php($c = $entityColor('available-images'))
    
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <a href="{{ route('available-images.index') }}" class="mr-4 p-2 rounded-md text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 {{ $c['focus'] }}">
                    <x-heroicon-o-arrow-left class="h-5 w-5" />
                </a>
                <div class="flex items-center">
                    <x-heroicon-o-photo class="h-8 w-8 mr-3 {{ $c['text'] }}" />
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Image Details</h1>
                        <p class="text-sm text-gray-500">View image information and metadata</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Image Display -->
    <div class="bg-white rounded-lg shadow mb-6">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-4">Image</h2>
            
            <div class="w-full bg-gray-50 rounded-lg overflow-hidden flex items-center justify-center" style="min-height: 400px; max-height: 600px;">
                <img 
                    src="{{ route('available-images.view', $availableImage) }}" 
                    alt="{{ $availableImage->comment ?: 'Image' }}"
                    class="max-h-full max-w-full object-contain"
                    onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22400%22 height=%22400%22%3E%3Crect fill=%22%23f3f4f6%22 width=%22400%22 height=%22400%22/%3E%3Ctext fill=%22%239ca3af%22 font-family=%22sans-serif%22 font-size=%2224%22 text-anchor=%22middle%22 x=%22200%22 y=%22200%22%3EImage not found%3C/text%3E%3C/svg%3E'"
                />
            </div>

            <!-- Image Actions -->
            <div class="mt-4 flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <a 
                        href="{{ route('available-images.download', $availableImage) }}" 
                        class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white {{ $c['button'] }} focus:outline-none focus:ring-2 focus:ring-offset-2 {{ $c['focus'] }}"
                        download
                    >
                        <x-heroicon-o-arrow-down-tray class="h-4 w-4 mr-2" />
                        Download
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Image Information -->
    <div class="bg-white rounded-lg shadow">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-4">Information</h2>
            
            <dl class="divide-y divide-gray-200">
                <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                    <dt class="text-sm font-medium text-gray-500">ID</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0 font-mono">
                        {{ $availableImage->id }}
                    </dd>
                </div>

                <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                    <dt class="text-sm font-medium text-gray-500">Comment</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">
                        {{ $availableImage->comment ?: '—' }}
                    </dd>
                </div>

                <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                    <dt class="text-sm font-medium text-gray-500">Path</dt>
                    <dd class="mt-1 text-xs text-gray-900 sm:col-span-2 sm:mt-0 font-mono break-all">
                        {{ $availableImage->path }}
                    </dd>
                </div>

                <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                    <dt class="text-sm font-medium text-gray-500">Created</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">
                        {{ $availableImage->created_at->format('F d, Y \a\t H:i:s') }}
                    </dd>
                </div>

                <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                    <dt class="text-sm font-medium text-gray-500">Updated</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">
                        {{ $availableImage->updated_at->format('F d, Y \a\t H:i:s') }}
                    </dd>
                </div>
            </dl>
        </div>
    </div>

    <!-- Back Button -->
    <div class="mt-6 flex justify-end">
        <a href="{{ route('available-images.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 {{ $c['focus'] }}">
            <x-heroicon-o-arrow-left class="h-4 w-4 mr-2" />
            Back to Images
        </a>
    </div>
</div>
@endsection
