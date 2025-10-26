@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    @php($c = $entityColor('available-images'))
    
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center">
            <a href="{{ route('available-images.show', $availableImage) }}" class="mr-4 p-2 rounded-md text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 {{ $c['focus'] }}">
                <x-heroicon-o-arrow-left class="h-5 w-5" />
            </a>
            <div class="flex items-center">
                <x-heroicon-o-photo class="h-8 w-8 mr-3 {{ $c['text'] }}" />
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Edit Image</h1>
                    <p class="text-sm text-gray-500">Update image information</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Image Preview -->
    <div class="bg-white rounded-lg shadow mb-6">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-4">Image Preview</h2>
            
            <div class="w-full bg-gray-50 rounded-lg overflow-hidden flex items-center justify-center" style="max-height: 300px;">
                <img 
                    src="{{ route('available-images.view', $availableImage) }}" 
                    alt="{{ $availableImage->comment ?: 'Image' }}"
                    class="max-h-full max-w-full object-contain"
                />
            </div>
        </div>
    </div>

    <!-- Edit Form -->
    <form method="POST" action="{{ route('available-images.update', $availableImage) }}">
        @csrf
        @method('PUT')

        <div class="bg-white rounded-lg shadow">
            <div class="p-6 space-y-6">
                <!-- Comment Field -->
                <x-form.field label="Comment" name="comment">
                    <x-form.textarea
                        name="comment"
                        :value="old('comment', $availableImage->comment)"
                        rows="3"
                        placeholder="Optional description or comment about this image"
                    />
                    <p class="mt-1 text-sm text-gray-500">Add a description or notes about this image (max 500 characters)</p>
                </x-form.field>

                <!-- Read-only Information -->
                <div class="pt-4 border-t border-gray-200">
                    <h3 class="text-sm font-medium text-gray-700 mb-3">Image Information</h3>
                    <dl class="space-y-2">
                        <div>
                            <dt class="text-xs text-gray-500">ID</dt>
                            <dd class="text-sm text-gray-900 font-mono">{{ $availableImage->id }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-gray-500">Path</dt>
                            <dd class="text-xs text-gray-900 font-mono break-all">{{ $availableImage->path }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-gray-500">Created</dt>
                            <dd class="text-sm text-gray-900">{{ $availableImage->created_at->format('F d, Y \a\t H:i:s') }}</dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="mt-6 flex items-center justify-between">
            <a href="{{ route('available-images.show', $availableImage) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 {{ $c['focus'] }}">
                Cancel
            </a>
            <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white {{ $c['button'] }} focus:outline-none focus:ring-2 focus:ring-offset-2 {{ $c['focus'] }}">
                <x-heroicon-o-check class="h-4 w-4 mr-2" />
                Save Changes
            </button>
        </div>
    </form>
</div>
@endsection
