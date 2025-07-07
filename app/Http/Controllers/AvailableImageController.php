<?php

namespace App\Http\Controllers;

use App\Http\Resources\AvailableImageResource;
use App\Models\AvailableImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AvailableImageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return AvailableImageResource::collection(AvailableImage::all());
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AvailableImage $availableImage)
    {
        $validated = $request->validate([
            /** @ignoreParam */
            'id' => 'prohibited',
            /** @ignoreParam */
            'path' => 'prohibited',
            'comment' => 'nullable|string',
        ]);
        $availableImage->update($validated);
        $availableImage->refresh();

        return new AvailableImageResource($availableImage);
    }

    /**
     * Display the specified resource.
     */
    public function show(AvailableImage $availableImage)
    {
        return new AvailableImageResource($availableImage);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AvailableImage $availableImage)
    {
        Storage::delete($availableImage->path);
        $availableImage->delete();

        return response()->noContent();
    }

    /**
     * Returns the file to the caller.
     */
    public function download(AvailableImage $availableImage)
    {
        $disk = config('localstorage.public.images.disk');
        $path = $availableImage->path;

        if (! Storage::disk($disk)->exists($path)) {
            abort(404, 'Image not found');
        }

        $fullPath = Storage::disk($disk)->path($path);
        $filename = basename($path);

        return response()->download($fullPath, $filename);
    }

    /**
     * Returns the image file for direct viewing (e.g., for use in <img> src attribute).
     */
    public function view(AvailableImage $availableImage)
    {
        $disk = config('localstorage.public.images.disk');
        $path = $availableImage->path;

        if (! Storage::disk($disk)->exists($path)) {
            abort(404, 'Image not found');
        }

        $fullPath = Storage::disk($disk)->path($path);
        $mimeType = mime_content_type($fullPath);

        return response()->file($fullPath, [
            'Content-Type' => $mimeType,
            'Cache-Control' => 'public, max-age=3600',
            'Content-Disposition' => 'inline',
        ]);
    }
}
