<?php

namespace App\Http\Controllers\Web;

use App\Enums\Permission;
use App\Events\ImageUploadEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\Web\StoreImageUploadRequest;
use App\Models\ImageUpload;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ImageUploadController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:'.Permission::CREATE_DATA->value)->only(['create', 'store']);
    }

    /**
     * Show the form for uploading images.
     */
    public function create(): View
    {
        return view('images.upload');
    }

    /**
     * Store uploaded images.
     */
    public function store(StoreImageUploadRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        // Store the file in the local/private directory and disk
        $file = $request->file('file');
        $path = $file->store(config('localstorage.uploads.images.directory'), config('localstorage.uploads.images.disk'));

        $imageUpload = ImageUpload::create([
            'path' => $path,
            'name' => $file->getClientOriginalName(),
            'extension' => $file->getClientOriginalExtension(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
        ]);

        ImageUploadEvent::dispatch($imageUpload);

        return redirect()->route('images.upload')->with('success', 'Image uploaded successfully and is being processed.');
    }
}
