<?php

namespace App\Http\Controllers;

use App\Http\Requests\Api\IndexCollectionTranslationRequest;
use App\Http\Requests\Api\ShowCollectionTranslationRequest;
use App\Http\Requests\Api\StoreCollectionTranslationRequest;
use App\Http\Requests\Api\UpdateCollectionTranslationRequest;
use App\Http\Resources\CollectionTranslationResource;
use App\Models\CollectionTranslation;

/**
 * @tags Collection Translations
 */
class CollectionTranslationController extends Controller
{
    /**
     * Display a listing of collection translations
     */
    public function index(IndexCollectionTranslationRequest $request)
    {
        $query = CollectionTranslation::query();

        // Allow filtering by collection_id, language_id, or context_id
        if ($request->has('collection_id')) {
            $query->where('collection_id', $request->collection_id);
        }

        if ($request->has('language_id')) {
            $query->where('language_id', $request->language_id);
        }

        if ($request->has('context_id')) {
            $query->where('context_id', $request->context_id);
        }

        // Include default context filter
        if ($request->boolean('default_context')) {
            $query->defaultContext();
        }

        $pagination = $request->getPaginationParams();
        $translations = $query->with(['collection', 'language', 'context'])
            ->paginate($pagination['per_page'], ['*'], 'page', $pagination['page']);

        return CollectionTranslationResource::collection($translations);
    }

    /**
     * Store a newly created collection translation
     */
    public function store(StoreCollectionTranslationRequest $request)
    {
        $translation = CollectionTranslation::create($request->validated());

        return new CollectionTranslationResource($translation->load(['collection', 'language', 'context']));
    }

    /**
     * Display the specified collection translation
     */
    public function show(ShowCollectionTranslationRequest $request, CollectionTranslation $collectionTranslation)
    {
        return new CollectionTranslationResource($collectionTranslation->load(['collection', 'language', 'context']));
    }

    /**
     * Update the specified collection translation
     */
    public function update(UpdateCollectionTranslationRequest $request, CollectionTranslation $collectionTranslation)
    {
        $collectionTranslation->update($request->validated());

        return new CollectionTranslationResource($collectionTranslation->load(['collection', 'language', 'context']));
    }

    /**
     * Remove the specified collection translation
     */
    public function destroy(CollectionTranslation $collectionTranslation)
    {
        $collectionTranslation->delete();

        return response()->noContent();
    }
}
