<?php

namespace App\Http\Controllers;

use App\Models\Picture;
use Illuminate\Http\Request;
use App\Events\PictureUploaded;
use App\Http\Resources\PictureResource;
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
            'file' => 'required|image|mimes:jpeg,png,jpg|max:16384',
        ]);

        $file = $request->file('file');
        $path = $request->file('file')->store('Pictures', 'local');
        /*$filename = (string) Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('Pictures', $filename, 'local');*/

        $picture = Picture::create([
            'path' => $path,
            'name' => $file->getClientOriginalName(),
            'extension' => $file->getClientOriginalExtension(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
        ]);
        PictureUploaded::dispatch($picture);
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
     * Remove the specified resource from storage.
     */
    public function destroy(Picture $picture)
    {
        Storage::delete($picture->path);
        $picture->delete();
        return response()->json(null, 204);
    }
}
