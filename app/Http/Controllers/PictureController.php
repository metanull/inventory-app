<?php

namespace App\Http\Controllers;

use App\Http\Resources\PictureResource;
use App\Models\Picture;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PictureController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return PictureResource::collection(Picture::all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            /** @ignoreParam */
            'id' => 'prohibited',
            'file' => 'required|image|mimes:jpeg,png,jpg|max:16384',
            'internal_name' => 'required|string',
            'backward_compatibility' => 'nullable|string',
            'copyright_text' => 'nullable|string',
            'copyright_url' => 'nullable|url',
            /** @ignoreParam */
            'path' => 'prohibited',
            /** @ignoreParam */
            'upload_name' => 'prohibited',
            /** @ignoreParam */
            'upload_extension' => 'prohibited',
            /** @ignoreParam */
            'upload_mime_type' => 'prohibited',
            /** @ignoreParam */
            'upload_size' => 'prohibited',
        ]);

        $file = $request->file('file');
        $path = $request->file('file')->store('Pictures', 'local');
        $validated['path'] = $path;
        /*$filename = (string) Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('Pictures', $filename, 'local');*/

        $picture = Picture::create($validated);
        $picture->refresh();
        $picture->update([
            'upload_name' => $file->getClientOriginalName(),
            'upload_extension' => $file->getClientOriginalExtension(),
            'upload_mime_type' => $file->getMimeType(),
            'upload_size' => $file->getSize(),
        ]);
        $picture->refresh();

        return new PictureResource($picture);
    }

    /**
     * Display the specified resource.
     */
    public function show(Picture $picture)
    {
        return new PictureResource($picture);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Picture $picture)
    {
        $validated = $request->validate([
            /** @ignoreParam */
            'id' => 'prohibited',
            'internal_name' => 'required|string',
            'backward_compatibility' => 'nullable|string',
            'copyright_text' => 'nullable|string',
            'copyright_url' => 'nullable|url',
            /** @ignoreParam */
            'path' => 'prohibited',
            /** @ignoreParam */
            'upload_name' => 'prohibited',
            /** @ignoreParam */
            'upload_extension' => 'prohibited',
            /** @ignoreParam */
            'upload_mime_type' => 'prohibited',
            /** @ignoreParam */
            'upload_size' => 'prohibited',
        ]);
        
        $picture->update($validated);
        $picture->refresh();

        return new PictureResource($picture);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Picture $picture)
    {
        Storage::delete($picture->path);
        $picture->delete();

        return response()->noContent();
    }
}
