<?php

namespace App\Http\Controllers;

use App\Http\Requests\ItemTranslation\IndexItemTranslationRequest;
use App\Http\Requests\ItemTranslation\ShowItemTranslationRequest;
use App\Http\Requests\ItemTranslation\StoreItemTranslationRequest;
use App\Http\Requests\ItemTranslation\UpdateItemTranslationRequest;
use App\Http\Resources\ItemTranslationResource;
use App\Models\ItemTranslation;
use Illuminate\Database\QueryException;
use Illuminate\Http\Response;

/**
 * @tags Item Translations
 */
class ItemTranslationController extends Controller
{
    /**
     * Display a listing of item translations
     *
     * @response ItemTranslationResource[]
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

        $translations = $query->with(['item', 'language', 'context', 'author', 'textCopyEditor', 'translator', 'translationCopyEditor'])
            ->paginate(15);

        return ItemTranslationResource::collection($translations);
    }

    /**
     * Store a newly created item translation
     *
     * @response 201 ItemTranslationResource
     */
    public function store(StoreItemTranslationRequest $request)
    {
        $data = $request->validated();

        try {
            $translation = ItemTranslation::create($data);
            $translation->load(['item', 'language', 'context', 'author', 'textCopyEditor', 'translator', 'translationCopyEditor']);

            return new ItemTranslationResource($translation);
        } catch (QueryException $e) {
            if ($e->getCode() === '23000') {
                return response()->json([
                    'message' => 'A translation for this item, language, and context combination already exists.',
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            throw $e;
        }
    }

    /**
     * Display the specified item translation
     *
     * @response ItemTranslationResource
     */
    public function show(ShowItemTranslationRequest $request, ItemTranslation $itemTranslation)
    {
        $itemTranslation->load(['item', 'language', 'context', 'author', 'textCopyEditor', 'translator', 'translationCopyEditor']);

        return new ItemTranslationResource($itemTranslation);
    }

    /**
     * Update the specified item translation
     *
     * @response ItemTranslationResource
     */
    public function update(UpdateItemTranslationRequest $request, ItemTranslation $itemTranslation)
    {
        $data = $request->validated();

        try {
            $itemTranslation->update($data);
            $itemTranslation->load(['item', 'language', 'context', 'author', 'textCopyEditor', 'translator', 'translationCopyEditor']);

            return new ItemTranslationResource($itemTranslation);
        } catch (QueryException $e) {
            if ($e->getCode() === '23000') {
                return response()->json([
                    'message' => 'A translation for this item, language, and context combination already exists.',
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            throw $e;
        }
    }

    /**
     * Remove the specified item translation
     *
     * @response 204
     */
    public function destroy(ItemTranslation $itemTranslation)
    {
        $itemTranslation->delete();

        return response()->noContent();
    }
}
