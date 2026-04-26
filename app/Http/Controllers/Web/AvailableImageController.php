<?php

namespace App\Http\Controllers\Web;

use App\Enums\Permission;
use App\Http\Controllers\Controller;
use App\Http\Requests\Web\IndexAvailableImageRequest;
use App\Http\Requests\Web\UpdateAvailableImageRequest;
use App\Http\Responses\Image\DownloadImageResponse;
use App\Http\Responses\Image\InlineImageResponse;
use App\Models\AvailableImage;
use App\Services\Web\AvailableImageIndexQuery;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;

class AvailableImageController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:'.Permission::VIEW_DATA->value)->only(['index', 'show', 'view']);
        $this->middleware('permission:'.Permission::UPDATE_DATA->value)->only(['edit', 'update']);
        $this->middleware('permission:'.Permission::DELETE_DATA->value)->only(['destroy']);
    }

    public function index(IndexAvailableImageRequest $request, AvailableImageIndexQuery $availableImageIndexQuery): View
    {
        $listState = $request->listState();

        return view('available-images.index', [
            'availableImages' => $availableImageIndexQuery->paginate($listState),
            'listState' => $listState,
        ]);
    }

    /**
     * Display the specified available image.
     */
    public function show(AvailableImage $availableImage): View
    {
        return view('available-images.show', compact('availableImage'));
    }

    /**
     * Returns the image file for direct viewing (e.g., for use in <img> src attribute).
     */
    public function view(AvailableImage $availableImage)
    {
        return new InlineImageResponse($availableImage);
    }

    /**
     * Returns the file to the caller for download.
     */
    public function download(AvailableImage $availableImage)
    {
        return new DownloadImageResponse($availableImage);
    }

    /**
     * Show the form for editing the specified available image.
     */
    public function edit(AvailableImage $availableImage): View
    {
        return view('available-images.edit', compact('availableImage'));
    }

    /**
     * Update the specified available image in storage.
     */
    public function update(UpdateAvailableImageRequest $request, AvailableImage $availableImage): RedirectResponse
    {
        $availableImage->update($request->validated());

        return redirect()->route('available-images.show', $availableImage)
            ->with('success', 'Image comment updated successfully');
    }

    /**
     * Remove the specified available image from storage.
     */
    public function destroy(AvailableImage $availableImage): RedirectResponse
    {
        // Delete the physical file from storage
        $disk = Storage::disk(config('localstorage.available.images.disk'));
        if ($disk->exists($availableImage->path)) {
            $disk->delete($availableImage->path);
        }

        // Delete the database record
        $availableImage->delete();

        return redirect()->route('available-images.index')
            ->with('success', 'Image deleted successfully');
    }
}
