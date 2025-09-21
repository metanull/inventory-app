<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExhibitionTranslation\IndexExhibitionTranslationRequest;
use App\Http\Requests\ExhibitionTranslation\ShowExhibitionTranslationRequest;
use App\Http\Requests\ExhibitionTranslation\StoreExhibitionTranslationRequest;
use App\Http\Requests\ExhibitionTranslation\UpdateExhibitionTranslationRequest;
use App\Http\Resources\ExhibitionTranslationResource;
use App\Models\ExhibitionTranslation;
use Illuminate\Database\QueryException;
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
    public function index(IndexExhibitionTranslationRequest $request)
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
    public function store(StoreExhibitionTranslationRequest $request)
    {
        $validated = $request->validated();

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
    public function show(ShowExhibitionTranslationRequest $request, ExhibitionTranslation $exhibitionTranslation)
    {
        return new ExhibitionTranslationResource($exhibitionTranslation);
    }

    /**
     * Update the specified exhibition translation
     *
     * @response ExhibitionTranslationResource
     */
    public function update(UpdateExhibitionTranslationRequest $request, ExhibitionTranslation $exhibitionTranslation)
    {
        $validated = $request->validated();

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
