<?php

namespace App\Http\Controllers;

use App\Events\ImageUploadEvent;
use App\Http\Resources\ImageUploadResource;
use App\Models\ImageUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ImageUploadController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return ImageUploadResource::collection(ImageUpload::all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate the request, ensuring the 'file' is an image and other fields are not required.
        // This first validation is ensentially required for dedoc/scramble to properly detect that the 'file' parameter is a file upload.
        $validated = $request->validate([
            'file' => 'required|image',
            /** @ignoreParam */
            'id' => 'prohibited',
            /** @ignoreParam */
            'path' => 'prohibited',
            /** @ignoreParam */
            'name' => 'prohibited',
            /** @ignoreParam */
            'extension' => 'prohibited',
            /** @ignoreParam */
            'mime_type' => 'prohibited',
            /** @ignoreParam */
            'size' => 'prohibited',
        ]);

        // Validate the image upload rules from the configuration.
        // This second validation is necessary to ensure the image meets the specified criteria.
        // As the rules are defined at the runtime, they are not accessible to the static analysis tool (dedoc/scramble).
        // The rules are fetched from the configuration file, allowing for easy customization.
        // The default values are set to 'jpeg,png,jpg' for mime types and 20480 (20 MB) for max size.
        $imageUploadRules = [
            'mime' => config('localstorage.uploads.images.mime', 'jpeg,png,jpg'),
            'max_size' => config('localstorage.uploads.images.max_size', 20480),
        ];
        $validated = $request->validate([
            'file' => "required|image|mimes:{$imageUploadRules['mime']}|max:{$imageUploadRules['max_size']}",
        ]);

        // Store the file in the local/private directory and disk.
        $file = $request->file('file');
        $path = $file->store(config('localstorage.uploads.images.directory'), config('localstorage.uploads.images.disk'));

        $validated['path'] = $path;

        $imageUpload = ImageUpload::create($validated);
        $imageUpload->refresh();
        $imageUpload->update([
            'name' => $file->getClientOriginalName(),
            'extension' => $file->getClientOriginalExtension(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
        ]);
        $imageUpload->refresh();
        ImageUploadEvent::dispatch($imageUpload);

        return new ImageUploadResource($imageUpload);
    }

    /**
     * Display the specified resource.
     */
    public function show(ImageUpload $imageUpload)
    {
        return new ImageUploadResource($imageUpload);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ImageUpload $imageUpload)
    {
        Storage::delete($imageUpload->path);
        $imageUpload->delete();

        return response()->json(null, 204);
    }

}
