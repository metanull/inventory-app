<?php

namespace App\Http\Controllers;

use App\Http\Resources\CollectionResource;
use App\Models\Collection;
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
    public function index(): AnonymousResourceCollection
    {
        $collections = Collection::with(['language', 'context', 'translations', 'partners', 'items'])->get();

        return CollectionResource::collection($collections);
    }

    /**
     * Store a newly created collection in storage.
     */
    public function store(Request $request): CollectionResource
    {
        $request->validate([
            'internal_name' => 'required|string|max:255|unique:collections,internal_name',
            'language_id' => 'required|string|size:3|exists:languages,id',
            'context_id' => 'required|string|exists:contexts,id',
            'backward_compatibility' => 'nullable|string|max:255',
        ]);

        $collection = Collection::create($request->only([
            'internal_name',
            'language_id',
            'context_id',
            'backward_compatibility',
        ]));

        $collection->load(['language', 'context', 'translations', 'partners', 'items']);

        return new CollectionResource($collection);
    }

    /**
     * Display the specified collection.
     */
    public function show(Collection $collection): CollectionResource
    {
        $collection->load(['language', 'context', 'translations', 'partners', 'items']);

        return new CollectionResource($collection);
    }

    /**
     * Update the specified collection in storage.
     */
    public function update(Request $request, Collection $collection): CollectionResource
    {
        $request->validate([
            'internal_name' => 'sometimes|required|string|max:255|unique:collections,internal_name,'.$collection->id,
            'language_id' => 'sometimes|required|string|size:3|exists:languages,id',
            'context_id' => 'sometimes|required|string|exists:contexts,id',
            'backward_compatibility' => 'nullable|string|max:255',
        ]);

        $collection->update($request->only([
            'internal_name',
            'language_id',
            'context_id',
            'backward_compatibility',
        ]));

        $collection->load(['language', 'context', 'translations', 'partners', 'items']);

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
