<?php

namespace App\Http\Controllers;

use App\Events\ImageUploadEvent;
use App\Http\Resources\ImageUploadResource;
use App\Models\AvailableImage;
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
        // This first validation is essentially required for dedoc/scramble to properly detect that the 'file' parameter is a file upload.
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
        $request->validate([
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
     * Get the processing status of an image upload.
     *
     * Returns the processing status. If processing is complete, returns the AvailableImage details.
     * If the ImageUpload no longer exists, check if an AvailableImage exists with the same ID.
     */
    public function status(string $id)
    {
        // First check if the ImageUpload still exists (processing not complete)
        $imageUpload = ImageUpload::find($id);
        if ($imageUpload) {
            return response()->json([
                'status' => 'processing',
                'available_image' => null,
            ]);
        }

        // Check if an AvailableImage exists with this ID (processing complete)
        $availableImage = AvailableImage::find($id);
        if ($availableImage) {
            return response()->json([
                'status' => 'processed',
                'available_image' => new \App\Http\Resources\AvailableImageResource($availableImage),
            ]);
        }

        // Neither exists - this could be an error state or the resource was never created
        return response()->json([
            'status' => 'not_found',
            'available_image' => null,
        ], 404);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ImageUpload $imageUpload)
    {
        Storage::delete($imageUpload->path);
        $imageUpload->delete();

        return response()->noContent();
    }
}
