<?php

namespace App\Http\Controllers;

use App\Http\Requests\Api\StoreCollectionMediaRequest;
use App\Http\Requests\Api\UpdateCollectionMediaRequest;
use App\Http\Resources\CollectionMediaResource;
use App\Models\Collection;
use App\Models\CollectionMedia;

class CollectionMediaController extends Controller
{
    /**
     * Display a listing of collection media for a specific collection.
     */
    public function index(Collection $collection)
    {
        $collectionMedia = $collection->collectionMedia()->orderBy('type')->orderBy('display_order')->get();

        return CollectionMediaResource::collection($collectionMedia);
    }

    /**
     * Store a newly created collection media.
     */
    public function store(StoreCollectionMediaRequest $request, Collection $collection)
    {
        $validated = $request->validated();
        $validated['collection_id'] = $collection->id;
        $validated['display_order'] ??= CollectionMedia::getNextDisplayOrderFor([
            'collection_id' => $collection->id,
            'type' => $validated['type'],
        ]);

        $collectionMedia = CollectionMedia::create($validated);

        return new CollectionMediaResource($collectionMedia);
    }

    /**
     * Display the specified collection media.
     */
    public function show(CollectionMedia $collectionMedia)
    {
        return new CollectionMediaResource($collectionMedia);
    }

    /**
     * Update the specified collection media.
     */
    public function update(UpdateCollectionMediaRequest $request, CollectionMedia $collectionMedia)
    {
        $validated = $request->validated();
        $collectionMedia->update($validated);
        $collectionMedia->refresh();

        return new CollectionMediaResource($collectionMedia);
    }

    /**
     * Move collection media up in display order.
     */
    public function moveUp(CollectionMedia $collectionMedia)
    {
        $collectionMedia->moveUp();
        $collectionMedia->refresh();

        return new CollectionMediaResource($collectionMedia);
    }

    /**
     * Move collection media down in display order.
     */
    public function moveDown(CollectionMedia $collectionMedia)
    {
        $collectionMedia->moveDown();
        $collectionMedia->refresh();

        return new CollectionMediaResource($collectionMedia);
    }

    /**
     * Remove the specified collection media.
     */
    public function destroy(CollectionMedia $collectionMedia)
    {
        $collectionMedia->delete();

        return response()->noContent();
    }
}
