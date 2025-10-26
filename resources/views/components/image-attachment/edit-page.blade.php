@props([
    'entity',           // The parent entity object (item, partner, etc.)
    'entityImage',      // The entity image object to edit
    'entityName',       // The entity name for display (e.g., 'item', 'partner')
    'updateRoute',      // Route to update the image
    'backRoute',        // Route to go back to
    'viewRoute',        // Route to view the image
])

@extends('layouts.app')

@section('content')
    <div class="min-h-screen bg-gray-50 py-8">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">
                            Edit Image Alt Text
                        </h1>
                        <p class="mt-2 text-sm text-gray-600">
                            Update the alternative text for this image
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
                <form method="POST" action="{{ $updateRoute }}" class="p-6 space-y-6">
                    @csrf
                    @method('PUT')

                    <!-- Image Preview -->
                    <div class="flex justify-center">
                        <div class="w-64 h-64 bg-gray-100 rounded-lg overflow-hidden">
                            <img src="{{ $viewRoute }}" 
                                 alt="{{ $entityImage->alt_text ?? ucfirst($entityName) . ' image' }}"
                                 class="w-full h-full object-contain">
                        </div>
                    </div>

                    <!-- Alt Text Field -->
                    <div>
                        <label for="alt_text" class="block text-sm font-medium text-gray-700">
                            Alternative Text
                        </label>
                        <input type="text" 
                               name="alt_text" 
                               id="alt_text" 
                               value="{{ old('alt_text', $entityImage->alt_text) }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                               placeholder="Descriptive text for accessibility">
                        @error('alt_text')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-2 text-sm text-gray-500">
                            Provide descriptive text for screen readers and when the image cannot be displayed.
                        </p>
                    </div>

                    @if(isset($entityImage->display_order))
                        <!-- Display Order Info -->
                        <div class="bg-gray-50 rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-700">Display Order:</span>
                                <span class="text-sm text-gray-600">{{ $entityImage->display_order }}</span>
                            </div>
                        </div>
                    @endif

                    <!-- Form Actions -->
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
                            Update Alt Text
                        </x-ui.button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
