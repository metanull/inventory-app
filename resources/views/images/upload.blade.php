@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    @php($c = $entityColor('images'))
    
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center">
            <a href="{{ route('web.welcome') }}" class="mr-4 p-2 rounded-md text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 {{ $c['focus'] }}">
                <x-heroicon-o-arrow-left class="h-5 w-5" />
            </a>
            <div class="flex items-center">
                <x-heroicon-o-cloud-arrow-up class="h-8 w-8 mr-3 {{ $c['text'] }}" />
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Image Upload</h1>
                    <p class="text-sm text-gray-500">Upload images for processing and validation</p>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <x-ui.alert :message="session('success')" type="success" entity="images" />
    @endif

    @if($errors->any())
        <x-ui.alert :message="$errors->first()" type="error" entity="images" />
    @endif

    <!-- Upload Form -->
    <div class="bg-white rounded-lg shadow">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-4">Upload Images</h2>

            <form action="{{ route('images.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf

                <!-- File Input -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Select Image
                    </label>
                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md hover:border-gray-400 transition-colors">
                        <div class="space-y-1 text-center">
                            <x-heroicon-o-photo class="mx-auto h-12 w-12 text-gray-400" />
                            <div class="flex text-sm text-gray-600">
                                <label for="file-upload" class="relative cursor-pointer rounded-md font-medium {{ $c['text'] }} hover:{{ $c['text'] }}/80 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 {{ $c['focus'] }}">
                                    <span>Upload a file</span>
                                    <input id="file-upload" name="file" type="file" accept="image/*" required class="sr-only">
                                </label>
                                <p class="pl-1">or drag and drop</p>
                            </div>
                            <p class="text-xs text-gray-500">
                                PNG, JPG, GIF up to {{ config('localstorage.uploads.images.max_size', 20480) / 1024 }}MB
                            </p>
                        </div>
                    </div>
                    @error('file')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end">
                    <a href="{{ route('web.welcome') }}" class="mr-3 inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 {{ $c['focus'] }}">
                        Cancel
                    </a>
                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white {{ $c['button'] }} hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 {{ $c['focus'] }}">
                        <x-heroicon-o-cloud-arrow-up class="h-4 w-4 mr-2" />
                        Upload Image
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Information Box -->
    <div class="mt-8">
        <x-ui.alert type="info" entity="available-images">
            <div class="flex">
                <div class="shrink-0">
                    <x-heroicon-o-information-circle class="h-5 w-5" />
                </div>
                <div class="ml-3 flex-1">
                    <h3 class="text-sm font-medium">
                        About Image Processing
                    </h3>
                    <div class="mt-2 text-sm">
                        <p>
                            After uploading, your image will be automatically validated and processed. 
                            Once processing is complete, the image will appear in the 
                            <a href="{{ route('available-images.index') }}" class="font-medium underline">Available Images</a> 
                            gallery where you can view, edit, and manage it.
                        </p>
                    </div>
                </div>
            </div>
        </x-ui.alert>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const dropZone = document.querySelector('.border-dashed');
    const fileInput = document.getElementById('file-upload');
    const fileLabel = fileInput.closest('label');

    // Prevent default drag behaviors
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, preventDefaults, false);
        document.body.addEventListener(eventName, preventDefaults, false);
    });

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    // Highlight drop zone when file is dragged over it
    ['dragenter', 'dragover'].forEach(eventName => {
        dropZone.addEventListener(eventName, highlight, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, unhighlight, false);
    });

    function highlight(e) {
        dropZone.classList.add('border-{{ $c["border"] ?? "indigo-500" }}', 'bg-{{ $c["bg"] ?? "indigo-50" }}');
    }

    function unhighlight(e) {
        dropZone.classList.remove('border-{{ $c["border"] ?? "indigo-500" }}', 'bg-{{ $c["bg"] ?? "indigo-50" }}');
    }

    // Handle dropped files
    dropZone.addEventListener('drop', handleDrop, false);

    function handleDrop(e) {
        const dt = e.dataTransfer;
        const files = dt.files;

        if (files.length > 0) {
            // Only take the first file
            const file = files[0];
            
            // Check if it's an image
            if (file.type.startsWith('image/')) {
                // Create a new FileList-like object
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(file);
                fileInput.files = dataTransfer.files;
                
                // Update label to show filename
                const filenameDisplay = document.createElement('p');
                filenameDisplay.className = 'text-sm {{ $c["text"] ?? "text-indigo-600" }} font-medium mt-2';
                filenameDisplay.textContent = `Selected: ${file.name}`;
                
                // Remove any existing filename display
                const existing = dropZone.querySelector('.text-sm.' + '{{ str_replace(" ", ".", $c["text"] ?? "text-indigo-600") }}'.split('.')[0]);
                if (existing && existing.textContent.startsWith('Selected:')) {
                    existing.remove();
                }
                
                dropZone.querySelector('.space-y-1').appendChild(filenameDisplay);
            } else {
                alert('Please upload an image file (PNG, JPG, GIF)');
            }
        }
    }

    // Also handle regular file input change
    fileInput.addEventListener('change', function(e) {
        if (this.files.length > 0) {
            const file = this.files[0];
            const filenameDisplay = document.createElement('p');
            filenameDisplay.className = 'text-sm {{ $c["text"] ?? "text-indigo-600" }} font-medium mt-2';
            filenameDisplay.textContent = `Selected: ${file.name}`;
            
            // Remove any existing filename display
            const existing = dropZone.querySelector('.text-sm.' + '{{ str_replace(" ", ".", $c["text"] ?? "text-indigo-600") }}'.split('.')[0]);
            if (existing && existing.textContent.startsWith('Selected:')) {
                existing.remove();
            }
            
            dropZone.querySelector('.space-y-1').appendChild(filenameDisplay);
        }
    });
});
</script>

@endsection
