<?php

namespace App\Http\Controllers;

use App\Http\Requests\Api\IndexCollectionRequest;
use App\Http\Requests\Api\ShowCollectionRequest;
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

        $defaults = ['language', 'context', 'translations', 'partners', 'items', 'attachedItems'];
        $with = array_values(array_unique(array_merge($defaults, $includes)));

        $query = Collection::query()->with($with);

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
    public function store(Request $request): CollectionResource
    {
        $request->validate([
            'internal_name' => 'required|string|max:255|unique:collections,internal_name',
            'type' => 'required|in:collection,exhibition,gallery',
            'language_id' => 'required|string|size:3|exists:languages,id',
            'context_id' => 'required|string|exists:contexts,id',
            'backward_compatibility' => 'nullable|string|max:255',
        ]);

        $collection = Collection::create($request->only([
            'internal_name',
            'type',
            'language_id',
            'context_id',
            'backward_compatibility',
        ]));

        $requested = IncludeParser::fromRequest($request, AllowList::for('collection'));
        $defaults = ['language', 'context', 'translations', 'partners', 'items', 'attachedItems'];
        $collection->load(array_values(array_unique(array_merge($defaults, $requested))));

        return new CollectionResource($collection);
    }

    /**
     * Display the specified collection.
     */
    public function show(ShowCollectionRequest $request, Collection $collection): CollectionResource
    {
        $includes = $request->getIncludeParams();
        if (! empty($includes)) {
            $collection->load($includes);
        }

        return new CollectionResource($collection);
    }

    /**
     * Update the specified collection in storage.
     */
    public function update(Request $request, Collection $collection): CollectionResource
    {
        $request->validate([
            'internal_name' => 'sometimes|required|string|max:255|unique:collections,internal_name,'.$collection->id,
            'type' => 'sometimes|required|in:collection,exhibition,gallery',
            'language_id' => 'sometimes|required|string|size:3|exists:languages,id',
            'context_id' => 'sometimes|required|string|exists:contexts,id',
            'backward_compatibility' => 'nullable|string|max:255',
        ]);

        $collection->update($request->only([
            'internal_name',
            'type',
            'language_id',
            'context_id',
            'backward_compatibility',
        ]));

        $requested = IncludeParser::fromRequest($request, AllowList::for('collection'));
        $defaults = ['language', 'context', 'translations', 'partners', 'items', 'attachedItems'];
        $collection->load(array_values(array_unique(array_merge($defaults, $requested))));

        return new CollectionResource($collection);
    }

    /**
     * Get collections by type.
     */
    public function byType(Request $request, string $type)
    {
        $request->validate([
            'type' => 'required|in:collection,exhibition,gallery',
        ]);

        $includes = IncludeParser::fromRequest($request, AllowList::for('collection'));
        $defaults = ['language', 'context', 'translations', 'partners', 'items', 'attachedItems'];
        $with = array_values(array_unique(array_merge($defaults, $includes)));

        $query = Collection::query()->with($with);

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
        }

        $collections = $query->get();

        return CollectionResource::collection($collections);
    }

    /**
     * Attach an item to a collection via many-to-many relationship.
     */
    public function attachItem(Request $request, Collection $collection)
    {
        $request->validate([
            'item_id' => 'required|uuid|exists:items,id',
        ]);

        $item = \App\Models\Item::findOrFail($request->item_id);
        $collection->attachItem($item);

        return response()->json([
            'success' => true,
            'message' => 'Item attached to collection successfully',
        ]);
    }

    /**
     * Detach an item from a collection.
     */
    public function detachItem(Request $request, Collection $collection)
    {
        $request->validate([
            'item_id' => 'required|uuid|exists:items,id',
        ]);

        $item = \App\Models\Item::findOrFail($request->item_id);
        $collection->detachItem($item);

        return response()->json([
            'success' => true,
            'message' => 'Item detached from collection successfully',
        ]);
    }

    /**
     * Attach multiple items to a collection.
     */
    public function attachItems(Request $request, Collection $collection)
    {
        $request->validate([
            'item_ids' => 'required|array|min:1',
            'item_ids.*' => 'required|uuid|exists:items,id',
        ]);

        $collection->attachItems($request->item_ids);

        return response()->json([
            'success' => true,
            'message' => 'Items attached to collection successfully',
        ]);
    }

    /**
     * Detach multiple items from a collection.
     */
    public function detachItems(Request $request, Collection $collection)
    {
        $request->validate([
            'item_ids' => 'required|array|min:1',
            'item_ids.*' => 'required|uuid|exists:items,id',
        ]);

        $collection->detachItems($request->item_ids);

        return response()->json([
            'success' => true,
            'message' => 'Items detached from collection successfully',
        ]);
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
