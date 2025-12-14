<?php

namespace App\Http\Controllers\Web;

use App\Enums\Permission;
use App\Http\Controllers\Controller;
use App\Http\Requests\Web\StoreCollectionImageRequest;
use App\Http\Requests\Web\UpdateCollectionImageRequest;
use App\Models\AvailableImage;
use App\Models\Collection;
use App\Models\CollectionImage;
use Illuminate\Http\RedirectResponse;

class CollectionImageController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:'.Permission::CREATE_DATA->value)->only(['create', 'store']);
        $this->middleware('permission:'.Permission::UPDATE_DATA->value)->only(['edit', 'update', 'move_up', 'move_down']);
        $this->middleware('permission:'.Permission::DELETE_DATA->value)->only(['destroy', 'detach']);
    }

    /**
     * Show form to attach available image to collection.
     */
    public function create(Collection $collection)
    {
        $availableImages = AvailableImage::orderBy('path')->get();

        return view('collection-images.create', compact('collection', 'availableImages'));
    }

    /**
     * Attach an available image to a collection.
     */
    public function store(StoreCollectionImageRequest $request, Collection $collection): RedirectResponse
    {
        $validated = $request->validated();
        $availableImage = AvailableImage::findOrFail($validated['available_image_id']);

        CollectionImage::attachFromAvailableImage($availableImage, $collection->id);

        return redirect()->route('collections.show', $collection)
            ->with('success', 'Image attached successfully');
    }

    /**
     * Show form to edit collection image.
     */
    public function edit(Collection $collection, CollectionImage $collectionImage)
    {
        // Ensure the image belongs to the collection
        if ($collectionImage->collection_id !== $collection->id) {
            abort(404);
        }

        return view('collection-images.edit', compact('collection', 'collectionImage'));
    }

    /**
     * Update the specified collection image.
     */
    public function update(UpdateCollectionImageRequest $request, Collection $collection, CollectionImage $collectionImage): RedirectResponse
    {
        // Ensure the image belongs to the collection
        if ($collectionImage->collection_id !== $collection->id) {
            abort(404);
        }

        $collectionImage->update($request->validated());

        return redirect()->route('collections.show', $collection)
            ->with('success', 'Image updated successfully');
    }

    /**
     * Move collection image up in display order.
     */
    public function moveUp(Collection $collection, CollectionImage $collectionImage): RedirectResponse
    {
        // Ensure the image belongs to the collection
        if ($collectionImage->collection_id !== $collection->id) {
            abort(404);
        }

        $collectionImage->moveUp();

        return redirect()->route('collections.show', $collection)
            ->with('success', 'Image moved up');
    }

    /**
     * Move collection image down in display order.
     */
    public function moveDown(Collection $collection, CollectionImage $collectionImage): RedirectResponse
    {
        // Ensure the image belongs to the collection
        if ($collectionImage->collection_id !== $collection->id) {
            abort(404);
        }

        $collectionImage->moveDown();

        return redirect()->route('collections.show', $collection)
            ->with('success', 'Image moved down');
    }

    /**
     * Detach a collection image and convert it back to available image.
     */
    public function detach(Collection $collection, CollectionImage $collectionImage): RedirectResponse
    {
        // Ensure the image belongs to the collection
        if ($collectionImage->collection_id !== $collection->id) {
            abort(404);
        }

        $collectionImage->detachToAvailableImage();

        return redirect()->route('collections.show', $collection)
            ->with('success', 'Image detached and returned to available images');
    }

    /**
     * Remove the specified collection image permanently.
     */
    public function destroy(Collection $collection, CollectionImage $collectionImage): RedirectResponse
    {
        // Ensure the image belongs to the collection
        if ($collectionImage->collection_id !== $collection->id) {
            abort(404);
        }

        $collectionImage->delete();

        return redirect()->route('collections.show', $collection)
            ->with('success', 'Image deleted permanently');
    }

    /**
     * Returns the file to the caller.
     */
    public function download(Collection $collection, CollectionImage $collectionImage)
    {
        // Ensure the image belongs to the collection
        if ($collectionImage->collection_id !== $collection->id) {
            abort(404);
        }

        $disk = config('localstorage.pictures.disk');
        $directory = trim(config('localstorage.pictures.directory'), '/');
        $filename = $collectionImage->original_name ?: basename($collectionImage->path);

        // Prepend directory to path
        $storagePath = $directory.'/'.$collectionImage->path;

        return \App\Http\Responses\FileResponse::download(
            $disk,
            $storagePath,
            $filename,
            $collectionImage->mime_type
        );
    }

    /**
     * Returns the image file for direct viewing (e.g., for use in <img> src attribute).
     */
    public function view(Collection $collection, CollectionImage $collectionImage)
    {
        // Ensure the image belongs to the collection
        if ($collectionImage->collection_id !== $collection->id) {
            abort(404);
        }

        $disk = config('localstorage.pictures.disk');
        $directory = trim(config('localstorage.pictures.directory'), '/');

        // Prepend directory to path
        $storagePath = $directory.'/'.$collectionImage->path;

        return \App\Http\Responses\FileResponse::view(
            $disk,
            $storagePath,
            $collectionImage->mime_type
        );
    }
}
