<?php

namespace App\Http\Controllers;

use App\Http\Resources\ItemTranslationResource;
use App\Models\ItemTranslation;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
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
    public function index(Request $request)
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
    public function store(Request $request)
    {
        $data = $request->validate([
            'item_id' => 'required|uuid|exists:items,id',
            'language_id' => 'required|string|size:3|exists:languages,id',
            'context_id' => 'required|uuid|exists:contexts,id',
            'name' => 'required|string|max:255',
            'alternate_name' => 'nullable|string|max:255',
            'description' => 'required|string',
            'type' => 'nullable|string|max:255',
            'holder' => 'nullable|string',
            'owner' => 'nullable|string',
            'initial_owner' => 'nullable|string',
            'dates' => 'nullable|string',
            'location' => 'nullable|string',
            'dimensions' => 'nullable|string',
            'place_of_production' => 'nullable|string',
            'method_for_datation' => 'nullable|string',
            'method_for_provenance' => 'nullable|string',
            'obtention' => 'nullable|string',
            'bibliography' => 'nullable|string',
            'author_id' => 'nullable|uuid|exists:authors,id',
            'text_copy_editor_id' => 'nullable|uuid|exists:authors,id',
            'translator_id' => 'nullable|uuid|exists:authors,id',
            'translation_copy_editor_id' => 'nullable|uuid|exists:authors,id',
            'backward_compatibility' => 'nullable|string|max:255',
            'extra' => 'nullable|array',
        ]);

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
    public function show(ItemTranslation $itemTranslation)
    {
        $itemTranslation->load(['item', 'language', 'context', 'author', 'textCopyEditor', 'translator', 'translationCopyEditor']);

        return new ItemTranslationResource($itemTranslation);
    }

    /**
     * Update the specified item translation
     *
     * @response ItemTranslationResource
     */
    public function update(Request $request, ItemTranslation $itemTranslation)
    {
        $data = $request->validate([
            'item_id' => 'sometimes|uuid|exists:items,id',
            'language_id' => 'sometimes|string|size:3|exists:languages,id',
            'context_id' => 'sometimes|uuid|exists:contexts,id',
            'name' => 'sometimes|string|max:255',
            'alternate_name' => 'nullable|string|max:255',
            'description' => 'sometimes|string',
            'type' => 'nullable|string|max:255',
            'holder' => 'nullable|string',
            'owner' => 'nullable|string',
            'initial_owner' => 'nullable|string',
            'dates' => 'nullable|string',
            'location' => 'nullable|string',
            'dimensions' => 'nullable|string',
            'place_of_production' => 'nullable|string',
            'method_for_datation' => 'nullable|string',
            'method_for_provenance' => 'nullable|string',
            'obtention' => 'nullable|string',
            'bibliography' => 'nullable|string',
            'author_id' => 'nullable|uuid|exists:authors,id',
            'text_copy_editor_id' => 'nullable|uuid|exists:authors,id',
            'translator_id' => 'nullable|uuid|exists:authors,id',
            'translation_copy_editor_id' => 'nullable|uuid|exists:authors,id',
            'backward_compatibility' => 'nullable|string|max:255',
            'extra' => 'nullable|array',
        ]);

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
