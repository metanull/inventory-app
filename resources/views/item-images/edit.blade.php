@extends('layouts.app')

@section('content')
    <div class="min-h-screen bg-gray-50 dark:bg-gray-900 py-8">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                            Edit Image Alt Text
                        </h1>
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                            Update the alternative text for this image
                        </p>
                    </div>
                    <a href="{{ route('items.show', $item) }}" 
                       class="inline-flex items-center px-4 py-2 bg-gray-300 dark:bg-gray-700 border border-transparent rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest hover:bg-gray-400 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition ease-in-out duration-150">
                        Cancel
                    </a>
                </div>
            </div>

            <!-- Form -->
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg">
                <form method="POST" action="{{ route('items.item-images.update', [$item, $itemImage]) }}" class="p-6 space-y-6">
                    @csrf
                    @method('PUT')

                    <!-- Image Preview -->
                    <div class="flex justify-center">
                        <div class="w-64 h-64 bg-gray-100 dark:bg-gray-700 rounded-lg overflow-hidden">
                            <img src="{{ asset('storage/' . $itemImage->path) }}" 
                                 alt="{{ $itemImage->alt_text ?? 'Item image' }}"
                                 class="w-full h-full object-contain">
                        </div>
                    </div>

                    <!-- Alt Text Field -->
                    <div>
                        <label for="alt_text" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Alternative Text
                        </label>
                        <input type="text" 
                               name="alt_text" 
                               id="alt_text" 
                               value="{{ old('alt_text', $itemImage->alt_text) }}"
                               class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                               placeholder="Descriptive text for accessibility">
                        @error('alt_text')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                            Provide descriptive text for screen readers and when the image cannot be displayed.
                        </p>
                    </div>

                    <!-- Display Order Info -->
                    <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Display Order:</span>
                            <span class="text-sm text-gray-600 dark:text-gray-400">{{ $itemImage->display_order }}</span>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex items-center justify-end gap-3 pt-6 border-t border-gray-200 dark:border-gray-700">
                        <a href="{{ route('items.show', $item) }}" 
                           class="inline-flex items-center px-4 py-2 bg-gray-300 dark:bg-gray-700 border border-transparent rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest hover:bg-gray-400 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition ease-in-out duration-150">
                            Cancel
                        </a>
                        <button type="submit" 
                                class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition ease-in-out duration-150">
                            Update Image
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
