@extends('layouts.app')

@section('content')
    <div class="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <div class="mb-8">
            <x-entity.header 
                entity="images" 
                title="Image Upload"
                description="Upload images for processing and validation"
            />
        </div>

        @if(session('success'))
            <x-ui.alert :message="session('success')" type="success" entity="images" />
        @endif

        @if($errors->any())
            <x-ui.alert :message="$errors->first()" type="error" entity="images" />
        @endif

        <div class="bg-white rounded-lg shadow">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Upload Images</h2>

                <form action="{{ route('images.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf

                    <x-form.file-upload 
                        name="file"
                        label="Select Image"
                        accept="image/*"
                        required
                        entity="images"
                        :maxSize="config('localstorage.uploads.images.max_size', 20480)"
                    />

                    <x-form.actions 
                        :cancel-route="route('web.welcome')"
                        entity="images"
                        submit-text="Upload Image"
                    />
                </form>
            </div>
        </div>

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
@endsection
