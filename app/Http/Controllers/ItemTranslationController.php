<?php

namespace App\Http\Controllers;

use App\Http\Requests\Api\IndexItemTranslationRequest;
use App\Http\Requests\Api\ShowItemTranslationRequest;
use App\Http\Requests\Api\StoreItemTranslationRequest;
use App\Http\Requests\Api\UpdateItemTranslationRequest;
use App\Http\Resources\ItemTranslationResource;
use App\Models\ItemTranslation;

/**
 * @tags Item Translations
 */
class ItemTranslationController extends Controller
{
    /**
     * Display a listing of item translations
     */
    public function index(IndexItemTranslationRequest $request)
    {
        $query = ItemTranslation::query();

        // Allow filtering by item_id, language_id, or context_id
        if ($request->has('item_id')) {
            $query->where('item_id', $request->item_id);
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
        $translations = $query->with(['item', 'language', 'context', 'author', 'textCopyEditor', 'translator', 'translationCopyEditor'])
            ->paginate($pagination['per_page'], ['*'], 'page', $pagination['page']);

        return ItemTranslationResource::collection($translations);
    }

    /**
     * Store a newly created item translation
     *
     * @return ItemTranslationResource
     */
    public function store(StoreItemTranslationRequest $request)
    {
        $data = $request->validated();
        $translation = ItemTranslation::create($data);
        $translation->refresh();
        $translation->load(['item', 'language', 'context', 'author', 'textCopyEditor', 'translator', 'translationCopyEditor']);

        return new ItemTranslationResource($translation);
    }

    /**
     * Display the specified item translation
     */
    public function show(ShowItemTranslationRequest $request, ItemTranslation $itemTranslation)
    {
        $includes = $request->getIncludeParams();
        if (! empty($includes)) {
            $itemTranslation->load($includes);
        }

        return new ItemTranslationResource($itemTranslation);
    }

    /**
     * Update the specified item translation
     *
     * @return ItemTranslationResource
     */
    public function update(UpdateItemTranslationRequest $request, ItemTranslation $itemTranslation)
    {
        $data = $request->validated();
        $itemTranslation->update($data);
        $itemTranslation->refresh();
        $itemTranslation->load(['item', 'language', 'context', 'author', 'textCopyEditor', 'translator', 'translationCopyEditor']);

        return new ItemTranslationResource($itemTranslation);
    }

    /**
     * Remove the specified item translation
     */
    public function destroy(ItemTranslation $itemTranslation)
    {
        $itemTranslation->delete();

        return response()->noContent();
    }
}
