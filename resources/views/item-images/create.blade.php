@extends('layouts.app')

@section('content')
    <div class="min-h-screen bg-gray-50 dark:bg-gray-900 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                            Attach Image to {{ $item->internal_name }}
                        </h1>
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                            Select an available image to attach to this item
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
                <form method="POST" action="{{ route('items.item-images.store', $item) }}" class="p-6 space-y-6">
                    @csrf

                    <!-- Available Images Selection -->
                    <div class="space-y-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Select Available Image <span class="text-red-500">*</span>
                        </label>
                        
                        @if($availableImages->isEmpty())
                            <div class="text-center py-12 bg-gray-50 dark:bg-gray-900 rounded-lg">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No available images</h3>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Upload images to the available images pool first.</p>
                            </div>
                        @else
                            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                                @foreach($availableImages as $availableImage)
                                    <label class="relative cursor-pointer group">
                                        <input type="radio" 
                                               name="available_image_id" 
                                               value="{{ $availableImage->id }}" 
                                               class="peer sr-only" 
                                               {{ old('available_image_id') == $availableImage->id ? 'checked' : '' }}>
                                        
                                        <div class="aspect-square bg-gray-100 dark:bg-gray-700 rounded-lg overflow-hidden border-2 border-transparent peer-checked:border-blue-500 transition-all">
                                            <img src="{{ asset('storage/' . $availableImage->path) }}" 
                                                 alt="{{ $availableImage->comment ?? 'Available image' }}"
                                                 class="w-full h-full object-cover">
                                        </div>
                                        
                                        @if($availableImage->comment)
                                            <div class="mt-1 text-xs text-gray-600 dark:text-gray-400 truncate">
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
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Form Actions -->
                    @if($availableImages->isNotEmpty())
                        <div class="flex items-center justify-end gap-3 pt-6 border-t border-gray-200 dark:border-gray-700">
                            <a href="{{ route('items.show', $item) }}" 
                               class="inline-flex items-center px-4 py-2 bg-gray-300 dark:bg-gray-700 border border-transparent rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest hover:bg-gray-400 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition ease-in-out duration-150">
                                Cancel
                            </a>
                            <button type="submit" 
                                    class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition ease-in-out duration-150">
                                Attach Image
                            </button>
                        </div>
                    @endif
                </form>
            </div>
        </div>
    </div>
@endsection
