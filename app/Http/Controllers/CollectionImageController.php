<?php

namespace App\Http\Controllers;

use App\Http\Requests\Api\AttachFromAvailableCollectionImageRequest;
use App\Http\Requests\Api\IndexCollectionImageRequest;
use App\Http\Requests\Api\ShowCollectionImageRequest;
use App\Http\Requests\Api\StoreCollectionImageRequest;
use App\Http\Requests\Api\UpdateCollectionImageRequest;
use App\Http\Resources\CollectionImageResource;
use App\Models\AvailableImage;
use App\Models\Collection;
use App\Models\CollectionImage;

class CollectionImageController extends Controller
{
    /**
     * Display a listing of collection images for a specific collection.
     */
    public function index(IndexCollectionImageRequest $request, Collection $collection)
    {
        $includes = $request->getIncludeParams();
        $collectionImages = $collection->collectionImages()->orderBy('display_order');

        if (! empty($includes)) {
            $collectionImages->with($includes);
        }

        return CollectionImageResource::collection($collectionImages->get());
    }

    /**
     * Store a newly created collection image.
     */
    public function store(StoreCollectionImageRequest $request, Collection $collection)
    {
        $validated = $request->validated();
        $validated['collection_id'] = $collection->id;
        $validated['display_order'] = CollectionImage::getNextDisplayOrderForCollection($collection->id);

        $collectionImage = CollectionImage::create($validated);

        return new CollectionImageResource($collectionImage);
    }

    /**
     * Display the specified collection image.
     */
    public function show(ShowCollectionImageRequest $request, CollectionImage $collectionImage)
    {
        $includes = $request->getIncludeParams();

        if (! empty($includes)) {
            $collectionImage->load($includes);
        }

        return new CollectionImageResource($collectionImage);
    }

    /**
     * Update the specified collection image.
     */
    public function update(UpdateCollectionImageRequest $request, CollectionImage $collectionImage)
    {
        $validated = $request->validated();
        $collectionImage->update($validated);

        $includes = $request->getIncludeParams();
        if (! empty($includes)) {
            $collectionImage->load($includes);
        }

        return new CollectionImageResource($collectionImage);
    }

    /**
     * Move collection image up in display order.
     */
    public function moveUp(CollectionImage $collectionImage)
    {
        $collectionImage->moveUp();

        // Refresh the model to get updated data
        $collectionImage->refresh();

        return new CollectionImageResource($collectionImage);
    }

    /**
     * Move collection image down in display order.
     */
    public function moveDown(CollectionImage $collectionImage)
    {
        $collectionImage->moveDown();

        // Refresh the model to get updated data
        $collectionImage->refresh();

        return new CollectionImageResource($collectionImage);
    }

    /**
     * Tighten ordering for all images of the collection.
     */
    public function tightenOrdering(CollectionImage $collectionImage)
    {
        $collectionImage->tightenOrderingForCollection();

        return new \App\Http\Resources\OperationSuccessResource([
            'success' => true,
            'message' => 'Image ordering tightened successfully',
        ]);
    }

    /**
     * Attach an available image to a collection.
     *
     * @return CollectionImageResource
     */
    public function attachFromAvailable(AttachFromAvailableCollectionImageRequest $request, Collection $collection)
    {
        $validated = $request->validated();

        $availableImage = AvailableImage::findOrFail($validated['available_image_id']);
        $collectionImage = CollectionImage::attachFromAvailableImage($availableImage, $collection->id, $validated['alt_text'] ?? null);

        $includes = $request->getIncludeParams();
        if (! empty($includes)) {
            $collectionImage->load($includes);
        }

        return new CollectionImageResource($collectionImage);
    }

    /**
     * Detach a collection image and convert it back to available image.
     */
    public function detachToAvailable(CollectionImage $collectionImage)
    {
        $availableImage = $collectionImage->detachToAvailableImage();

        return new \App\Http\Resources\OperationSuccessResource([
            'success' => true,
            'message' => 'Image detached successfully',
            'available_image_id' => $availableImage->id,
        ]);
    }

    /**
     * Remove the specified collection image.
     */
    public function destroy(CollectionImage $collectionImage)
    {
        $collectionImage->delete();

        return response()->noContent();
    }

    /**
     * Returns the file to the caller.
     */
    public function download(CollectionImage $collectionImage)
    {
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
    public function view(CollectionImage $collectionImage)
    {
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
