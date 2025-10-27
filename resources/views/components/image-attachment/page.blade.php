@props([
    'entity',           // The entity object (item, partner, etc.)
    'entityName',       // The entity name for display (e.g., 'item', 'partner')
    'availableImages',  // Collection of available images
    'storeRoute',       // Route to store the attachment
    'backRoute',        // Route to go back to
])

@extends('layouts.app')

@section('content')
    <div class="min-h-screen bg-gray-50 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">
                            Attach Image to {{ $entity->internal_name }}
                        </h1>
                        <p class="mt-2 text-sm text-gray-600">
                            Select an available image to attach to this {{ $entityName }}
                        </p>
                    </div>
                    <x-ui.button 
                        href="{{ $backRoute }}" 
                        variant="secondary">
                        Cancel
                    </x-ui.button>
                </div>
            </div>

            <!-- Form -->
            <div class="bg-white shadow-sm rounded-lg">
                <form method="POST" action="{{ $storeRoute }}" class="p-6 space-y-6">
                    @csrf

                    <!-- Available Images Selection -->
                    <div class="space-y-4">
                        <label class="block text-sm font-medium text-gray-700">
                            Select Available Image <span class="text-red-500">*</span>
                        </label>
                        
                        @if($availableImages->isEmpty())
                            <x-ui.empty-state 
                                icon="photo"
                                title="No available images"
                                description="Upload images to the available images pool first."
                            />
                        @else
                            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                                @foreach($availableImages as $availableImage)
                                    <label class="relative cursor-pointer group">
                                        <input type="radio" 
                                               name="available_image_id" 
                                               value="{{ $availableImage->id }}" 
                                               class="peer sr-only" 
                                               {{ old('available_image_id') == $availableImage->id ? 'checked' : '' }}>
                                        
                                        <div class="aspect-square bg-gray-100 rounded-lg overflow-hidden border-2 border-transparent peer-checked:border-blue-500 transition-all">
                                            <img src="{{ route('available-images.view', $availableImage) }}" 
                                                 alt="{{ $availableImage->comment ?? 'Available image' }}"
                                                 class="w-full h-full object-cover">
                                        </div>
                                        
                                        @if($availableImage->comment)
                                            <div class="mt-1 text-xs text-gray-600 truncate">
                                                {{ $availableImage->comment }}
                                            </div>
                                        @endif
                                        
                                        <div class="absolute inset-0 flex items-center justify-center opacity-0 peer-checked:opacity-100 transition-opacity pointer-events-none">
                                            <div class="bg-blue-500 text-white rounded-full p-2">
                                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                </svg>
                                            </div>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        @endif

                        @error('available_image_id')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Form Actions -->
                    @if($availableImages->isNotEmpty())
                        <div class="flex items-center justify-end gap-3 pt-6 border-t border-gray-200">
                            <x-ui.button 
                                href="{{ $backRoute }}" 
                                variant="secondary">
                                Cancel
                            </x-ui.button>
                            <x-ui.button 
                                type="submit" 
                                variant="primary"
                                icon="check">
                                Attach Image
                            </x-ui.button>
                        </div>
                    @endif
                </form>
            </div>
        </div>
    </div>
@endsection
