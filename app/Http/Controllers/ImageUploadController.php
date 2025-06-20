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
        // $validateMime = config('localstorage.uploads.images.mime', 'jpg');
        // $validateSize = config('localstorage.uploads.images.max_size', 5242880);
        // $validated = $request->validate([
        //  'file' => "required|image|mimes:{$validateMime}|max:{$validateSize}",
        // ]);

        $validated = $request->validate([
            /** @ignoreParam */
            'id' => 'prohibited',
            'file' => 'required|image|mimes:jpeg,png,jpg|max:5242880',
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

    /**
     * Returns the file to the caller.
     */
    public function download(ImageUpload $imageUpload)
    {
        /*
            Opt 1:  // Works (as 'local' is the default disk)
                return Storage::download($imageUpload->path);
            Opt 2:  // Works, but implies messing with the path field
                $name = basename($imageUpload->path);
                $disk = config('localstorage.uploads.images.disk');
                $dir = trim(config('localstorage.uploads.images.directory'), '/');
                return Storage::disk($disk)->download($imageUpload->path);
            Opt 3:  // Works (as long as the path field is a relative path to the disk)
                return Storage::disk(config('localstorage.uploads.images.disk'))->download($imageUpload->path);
        */
        return Storage::disk(config('localstorage.uploads.images.disk'))->download($imageUpload->path);
    }
}
