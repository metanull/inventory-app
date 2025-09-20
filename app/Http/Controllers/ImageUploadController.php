<?php

namespace App\Http\Controllers;

use App\Events\ImageUploadEvent;
use App\Http\Requests\ImageUpload\IndexImageUploadRequest;
use App\Http\Requests\ImageUpload\StoreImageUploadRequest;
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
    public function index(IndexImageUploadRequest $request)
    {
        return ImageUploadResource::collection(ImageUpload::all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreImageUploadRequest $request)
    {
        // The Form Request handles all validation including:
        // - File validation (required, image, mimes, max size)
        // - Prohibited fields (id, path, name, extension, mime_type, size)
        // - Unexpected parameter rejection
        $validated = $request->validated();

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
