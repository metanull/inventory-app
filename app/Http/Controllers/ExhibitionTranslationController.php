<?php

namespace App\Http\Controllers;

use App\Http\Resources\ExhibitionTranslationResource;
use App\Models\ExhibitionTranslation;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * @tags Exhibition Translations
 */
class ExhibitionTranslationController extends Controller
{
    /**
     * Display a listing of exhibition translations
     *
     * @response ExhibitionTranslationResource[]
     */
    public function index(Request $request)
    {
        $query = ExhibitionTranslation::query();

        // Allow filtering by exhibition_id, language_id, or context_id
        if ($request->has('exhibition_id')) {
            $query->where('exhibition_id', $request->exhibition_id);
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

        $translations = $query->get();

        return ExhibitionTranslationResource::collection($translations);
    }

    /**
     * Store a newly created exhibition translation
     *
     * @response ExhibitionTranslationResource
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'exhibition_id' => 'required|uuid|exists:exhibitions,id',
            'language_id' => 'required|string|exists:languages,id',
            'context_id' => 'required|uuid|exists:contexts,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'url' => 'nullable|url|max:255',
            'backward_compatibility' => 'nullable|string|max:255',
            'extra' => 'nullable|json',
        ]);

        try {
            $translation = ExhibitionTranslation::create($validated);

            return new ExhibitionTranslationResource($translation);
        } catch (QueryException $e) {
            // Handle integrity constraint violations (SQLSTATE 23000 is database-agnostic)
            if ($e->getCode() === '23000') {
                return response()->json([
                    'message' => 'A translation for this exhibition, language and context combination already exists.',
                    'errors' => [
                        'exhibition_id' => ['A translation for this exhibition, language and context combination already exists.'],
                    ],
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
            throw $e;
        }
    }

    /**
     * Display the specified exhibition translation
     *
     * @response ExhibitionTranslationResource
     */
    public function show(ExhibitionTranslation $exhibitionTranslation)
    {
        return new ExhibitionTranslationResource($exhibitionTranslation);
    }

    /**
     * Update the specified exhibition translation
     *
     * @response ExhibitionTranslationResource
     */
    public function update(Request $request, ExhibitionTranslation $exhibitionTranslation)
    {
        $validated = $request->validate([
            'exhibition_id' => 'sometimes|required|uuid|exists:exhibitions,id',
            'language_id' => 'sometimes|required|string|exists:languages,id',
            'context_id' => 'sometimes|required|uuid|exists:contexts,id',
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'url' => 'nullable|url|max:255',
            'backward_compatibility' => 'nullable|string|max:255',
            'extra' => 'nullable|json',
        ]);

        try {
            $exhibitionTranslation->update($validated);

            return new ExhibitionTranslationResource($exhibitionTranslation);
        } catch (QueryException $e) {
            // Handle integrity constraint violations (SQLSTATE 23000 is database-agnostic)
            if ($e->getCode() === '23000') {
                return response()->json([
                    'message' => 'A translation for this exhibition, language and context combination already exists.',
                    'errors' => [
                        'exhibition_id' => ['A translation for this exhibition, language and context combination already exists.'],
                    ],
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
            throw $e;
        }
    }

    /**
     * Remove the specified exhibition translation
     *
     * @response 204
     */
    public function destroy(ExhibitionTranslation $exhibitionTranslation)
    {
        $exhibitionTranslation->delete();

        return response()->noContent();
    }
}
