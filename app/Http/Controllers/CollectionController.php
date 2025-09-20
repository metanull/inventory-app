<?php

namespace App\Http\Controllers;

use App\Http\Requests\Collection\IndexCollectionRequest;
use App\Http\Requests\Collection\ShowCollectionRequest;
use App\Http\Requests\Collection\StoreCollectionRequest;
use App\Http\Requests\Collection\UpdateCollectionRequest;
use App\Http\Resources\CollectionResource;
use App\Models\Collection;
use App\Support\Includes\AllowList;
use App\Support\Includes\IncludeParser;
use App\Support\Pagination\PaginationParams;
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
        $includes = IncludeParser::fromRequest($request, AllowList::for('collection'));
        $pagination = PaginationParams::fromRequest($request);

        $defaults = ['language', 'context', 'translations', 'partners', 'items'];
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
    public function store(StoreCollectionRequest $request): CollectionResource
    {
        $validated = $request->validated();

        $collection = Collection::create([
            'internal_name' => $validated['internal_name'],
            'language_id' => $validated['language_id'],
            'context_id' => $validated['context_id'],
            'backward_compatibility' => $validated['backward_compatibility'] ?? null,
        ]);

        $requested = IncludeParser::fromRequest($request, AllowList::for('collection'));
        $defaults = ['language', 'context', 'translations', 'partners', 'items'];
        $collection->load(array_values(array_unique(array_merge($defaults, $requested))));

        return new CollectionResource($collection);
    }

    /**
     * Display the specified collection.
     */
    public function show(ShowCollectionRequest $request, Collection $collection): CollectionResource
    {
        $includes = IncludeParser::fromRequest($request, AllowList::for('collection'));
        if (! empty($includes)) {
            $collection->load($includes);
        }

        return new CollectionResource($collection);
    }

    /**
     * Update the specified collection in storage.
     */
    public function update(UpdateCollectionRequest $request, Collection $collection): CollectionResource
    {
        $validated = $request->validated();

        $collection->update([
            'internal_name' => $validated['internal_name'] ?? $collection->internal_name,
            'language_id' => $validated['language_id'] ?? $collection->language_id,
            'context_id' => $validated['context_id'] ?? $collection->context_id,
            'backward_compatibility' => $validated['backward_compatibility'] ?? $collection->backward_compatibility,
        ]);

        $requested = IncludeParser::fromRequest($request, AllowList::for('collection'));
        $defaults = ['language', 'context', 'translations', 'partners', 'items'];
        $collection->load(array_values(array_unique(array_merge($defaults, $requested))));

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
