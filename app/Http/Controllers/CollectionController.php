<?php

namespace App\Http\Controllers;

use App\Http\Requests\Api\AttachItemCollectionRequest;
use App\Http\Requests\Api\AttachItemsCollectionRequest;
use App\Http\Requests\Api\DetachItemCollectionRequest;
use App\Http\Requests\Api\DetachItemsCollectionRequest;
use App\Http\Requests\Api\IndexCollectionRequest;
use App\Http\Requests\Api\ShowCollectionRequest;
use App\Http\Requests\Api\StoreCollectionRequest;
use App\Http\Requests\Api\UpdateCollectionRequest;
use App\Http\Resources\CollectionResource;
use App\Models\Collection;
use App\Support\Includes\AllowList;
use App\Support\Includes\IncludeParser;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

/**
 * Collection Controller
 *
 * Handles CRUD operations for Collections.
 * Provides REST API endpoints for managing museum item collections.
 */
class CollectionController extends Controller
{
    /**
     * Display a listing of the collections.
     */
    public function index(IndexCollectionRequest $request): AnonymousResourceCollection
    {
        $includes = $request->getIncludeParams();
        $pagination = $request->getPaginationParams();

        $query = Collection::query()->with($includes);

        $paginator = $query->paginate(
            $pagination['per_page'],
            ['*'],
            'page',
            $pagination['page']
        );

        return CollectionResource::collection($paginator);
    }

    /**
     * Store a newly created collection in storage.
     */
    public function store(StoreCollectionRequest $request): CollectionResource
    {
        $validated = $request->validated();

        $collection = Collection::create($validated);
        $collection->refresh();

        $includes = IncludeParser::fromRequest($request, AllowList::for('collection'));
        $collection->load($includes);

        return new CollectionResource($collection);
    }

    /**
     * Display the specified collection.
     */
    public function show(ShowCollectionRequest $request, Collection $collection): CollectionResource
    {
        $includes = $request->getIncludeParams();
        $collection->load($includes);

        return new CollectionResource($collection);
    }

    /**
     * Update the specified collection in storage.
     */
    public function update(UpdateCollectionRequest $request, Collection $collection): CollectionResource
    {
        $validated = $request->validated();

        $collection->update($validated);
        $collection->refresh();

        $includes = IncludeParser::fromRequest($request, AllowList::for('collection'));
        $collection->load($includes);

        return new CollectionResource($collection);
    }

    /**
     * Get collections by type.
     */
    public function byType(Request $request, string $type)
    {
        $request->validate([
            'type' => 'required|in:collection,exhibition,gallery,theme,exhibition trail,itinerary,location',
        ]);

        $includes = IncludeParser::fromRequest($request, AllowList::for('collection'));

        $query = Collection::query()->with($includes);

        switch ($type) {
            case 'collection':
                $query->collections();
                break;
            case 'exhibition':
                $query->exhibitions();
                break;
            case 'gallery':
                $query->galleries();
                break;
            case 'theme':
                $query->themes();
                break;
            case 'exhibition trail':
                $query->exhibitionTrails();
                break;
            case 'itinerary':
                $query->itineraries();
                break;
            case 'location':
                $query->locations();
                break;
        }

        $collections = $query->get();

        return CollectionResource::collection($collections);
    }

    /**
     * Attach an item to a collection via many-to-many relationship.
     *
     * @return CollectionResource
     */
    public function attachItem(AttachItemCollectionRequest $request, Collection $collection)
    {
        $validated = $request->validated();

        $item = \App\Models\Item::findOrFail($validated['item_id']);
        $collection->attachItem($item);

        $collection->refresh();
        $includes = $request->getIncludeParams();
        $collection->load($includes);

        return new CollectionResource($collection);
    }

    /**
     * Detach an item from a collection.
     *
     * @return CollectionResource
     */
    public function detachItem(DetachItemCollectionRequest $request, Collection $collection)
    {
        $validated = $request->validated();

        $item = \App\Models\Item::findOrFail($validated['item_id']);
        $collection->detachItem($item);

        $collection->refresh();
        $includes = $request->getIncludeParams();
        $collection->load($includes);

        return new CollectionResource($collection);
    }

    /**
     * Attach multiple items to a collection.
     *
     * @return CollectionResource
     */
    public function attachItems(AttachItemsCollectionRequest $request, Collection $collection)
    {
        $validated = $request->validated();

        $collection->attachItems($validated['item_ids']);

        $collection->refresh();
        $includes = $request->getIncludeParams();
        $collection->load($includes);

        return new CollectionResource($collection);
    }

    /**
     * Detach multiple items from a collection.
     *
     * @return CollectionResource
     */
    public function detachItems(DetachItemsCollectionRequest $request, Collection $collection)
    {
        $validated = $request->validated();

        $collection->detachItems($validated['item_ids']);

        $collection->refresh();
        $includes = $request->getIncludeParams();
        $collection->load($includes);

        return new CollectionResource($collection);
    }

    /**
     * Remove the specified collection from storage.
     */
    public function destroy(Collection $collection): Response
    {
        $collection->delete();

        return response()->noContent();
    }
}
