<?php

namespace App\Http\Controllers;

use App\Http\Resources\DetailTranslationResource;
use App\Models\DetailTranslation;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * @tags Detail Translations
 */
class DetailTranslationController extends Controller
{
    /**
     * Display a listing of detail translations
     *
     * @response DetailTranslationResource[]
     */
    public function index(Request $request)
    {
        $query = DetailTranslation::query();

        // Allow filtering by detail_id, language_id, or context_id
        if ($request->has('detail_id')) {
            $query->where('detail_id', $request->detail_id);
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

        $translations = $query->with(['detail', 'language', 'context', 'author', 'textCopyEditor', 'translator', 'translationCopyEditor'])
            ->paginate(15);

        return DetailTranslationResource::collection($translations);
    }

    /**
     * Store a newly created detail translation
     *
     * @response 201 DetailTranslationResource
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'detail_id' => 'required|uuid|exists:details,id',
            'language_id' => 'required|string|size:3|exists:languages,id',
            'context_id' => 'required|uuid|exists:contexts,id',
            'name' => 'required|string|max:255',
            'alternate_name' => 'nullable|string|max:255',
            'description' => 'required|string',
            'author_id' => 'nullable|uuid|exists:authors,id',
            'text_copy_editor_id' => 'nullable|uuid|exists:authors,id',
            'translator_id' => 'nullable|uuid|exists:authors,id',
            'translation_copy_editor_id' => 'nullable|uuid|exists:authors,id',
            'backward_compatibility' => 'nullable|string|max:255',
            'extra' => 'nullable|array',
        ]);

        try {
            $translation = DetailTranslation::create($data);
            $translation->load(['detail', 'language', 'context', 'author', 'textCopyEditor', 'translator', 'translationCopyEditor']);

            return new DetailTranslationResource($translation);
        } catch (QueryException $e) {
            if ($e->getCode() === '23000') {
                return response()->json([
                    'message' => 'A translation for this detail, language, and context combination already exists.',
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            throw $e;
        }
    }

    /**
     * Display the specified detail translation
     *
     * @response DetailTranslationResource
     */
    public function show(DetailTranslation $detailTranslation)
    {
        $detailTranslation->load(['detail', 'language', 'context', 'author', 'textCopyEditor', 'translator', 'translationCopyEditor']);

        return new DetailTranslationResource($detailTranslation);
    }

    /**
     * Update the specified detail translation
     *
     * @response DetailTranslationResource
     */
    public function update(Request $request, DetailTranslation $detailTranslation)
    {
        $data = $request->validate([
            'detail_id' => 'sometimes|uuid|exists:details,id',
            'language_id' => 'sometimes|string|size:3|exists:languages,id',
            'context_id' => 'sometimes|uuid|exists:contexts,id',
            'name' => 'sometimes|string|max:255',
            'alternate_name' => 'nullable|string|max:255',
            'description' => 'sometimes|string',
            'author_id' => 'nullable|uuid|exists:authors,id',
            'text_copy_editor_id' => 'nullable|uuid|exists:authors,id',
            'translator_id' => 'nullable|uuid|exists:authors,id',
            'translation_copy_editor_id' => 'nullable|uuid|exists:authors,id',
            'backward_compatibility' => 'nullable|string|max:255',
            'extra' => 'nullable|array',
        ]);

        try {
            $detailTranslation->update($data);
            $detailTranslation->load(['detail', 'language', 'context', 'author', 'textCopyEditor', 'translator', 'translationCopyEditor']);

            return new DetailTranslationResource($detailTranslation);
        } catch (QueryException $e) {
            if ($e->getCode() === '23000') {
                return response()->json([
                    'message' => 'A translation for this detail, language, and context combination already exists.',
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            throw $e;
        }
    }

    /**
     * Remove the specified detail translation
     *
     * @response 204
     */
    public function destroy(DetailTranslation $detailTranslation)
    {
        $detailTranslation->delete();

        return response()->noContent();
    }
}
