<div class="mt-8">
    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Images</h2>
                <a href="{{ route('items.item-images.create', $item) }}" 
                   class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Attach Image
                </a>
            </div>
        </div>

        <div class="p-6">
            @php
                $images = $item->itemImages()->orderBy('display_order')->get();
            @endphp

            @if($images->isEmpty())
                <div class="text-center py-12 bg-gray-50 dark:bg-gray-900 rounded-lg">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No images</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Get started by attaching an image to this item.</p>
                    <div class="mt-6">
                        <a href="{{ route('items.item-images.create', $item) }}" 
                           class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition ease-in-out duration-150">
                            Attach First Image
                        </a>
                    </div>
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($images as $image)
                        <div class="bg-gray-50 dark:bg-gray-900 rounded-lg overflow-hidden">
                            <!-- Image -->
                            <div class="aspect-square bg-gray-200 dark:bg-gray-700">
                                <img src="{{ route('items.item-images.view', [$item, $image]) }}" 
                                     alt="{{ $image->alt_text ?? 'Item image' }}"
                                     class="w-full h-full object-cover">
                            </div>

                            <!-- Info & Actions -->
                            <div class="p-4 space-y-3">
                                <!-- Alt Text -->
                                <div>
                                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Alt Text</p>
                                    <p class="text-sm text-gray-900 dark:text-white">
                                        {{ $image->alt_text ?: 'No alt text' }}
                                    </p>
                                </div>

                                <!-- Display Order -->
                                <div>
                                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Display Order</p>
                                    <p class="text-sm text-gray-900 dark:text-white">{{ $image->display_order }}</p>
                                </div>

                                <!-- Actions -->
                                <div class="flex flex-wrap gap-2 pt-2 border-t border-gray-200 dark:border-gray-700">
                                    <!-- Edit -->
                                    <a href="{{ route('items.item-images.edit', [$item, $image]) }}" 
                                       class="inline-flex items-center px-3 py-1.5 bg-blue-600 border border-transparent rounded text-xs text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition">
                                        <svg class="w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                        Edit
                                    </a>

                                    <!-- Move Up -->
                                    <form method="POST" action="{{ route('items.item-images.move-up', [$item, $image]) }}" class="inline">
                                        @csrf
                                        <button type="submit" 
                                                class="inline-flex items-center px-3 py-1.5 bg-gray-600 border border-transparent rounded text-xs text-white hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition">
                                            <svg class="w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                            </svg>
                                            Up
                                        </button>
                                    </form>

                                    <!-- Move Down -->
                                    <form method="POST" action="{{ route('items.item-images.move-down', [$item, $image]) }}" class="inline">
                                        @csrf
                                        <button type="submit" 
                                                class="inline-flex items-center px-3 py-1.5 bg-gray-600 border border-transparent rounded text-xs text-white hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition">
                                            <svg class="w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                            </svg>
                                            Down
                                        </button>
                                    </form>

                                    <!-- Detach -->
                                    <form method="POST" action="{{ route('items.item-images.detach', [$item, $image]) }}" class="inline" onsubmit="return confirm('Detach this image and return it to available images?');">
                                        @csrf
                                        <button type="submit" 
                                                class="inline-flex items-center px-3 py-1.5 bg-yellow-600 border border-transparent rounded text-xs text-white hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 transition">
                                            <svg class="w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
                                            </svg>
                                            Detach
                                        </button>
                                    </form>

                                    <!-- Delete -->
                                    <form method="POST" action="{{ route('items.item-images.destroy', [$item, $image]) }}" class="inline" onsubmit="return confirm('Permanently delete this image? This action cannot be undone.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="inline-flex items-center px-3 py-1.5 bg-red-600 border border-transparent rounded text-xs text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition">
                                            <svg class="w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
