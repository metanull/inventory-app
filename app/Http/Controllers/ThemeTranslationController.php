<?php

namespace App\Http\Controllers;

use App\Http\Requests\ThemeTranslation\IndexThemeTranslationRequest;
use App\Http\Requests\ThemeTranslation\StoreThemeTranslationRequest;
use App\Http\Requests\ThemeTranslation\UpdateThemeTranslationRequest;
use App\Http\Resources\ThemeTranslationResource;
use App\Models\ThemeTranslation;
use Illuminate\Database\QueryException;
use Illuminate\Http\Response;

/**
 * @tags Theme Translations
 */
class ThemeTranslationController extends Controller
{
    /**
     * Display a listing of theme translations
     *
     * @response ThemeTranslationResource[]
     */
    public function index(IndexThemeTranslationRequest $request)
    {
        $query = ThemeTranslation::query();

        // Allow filtering by theme_id, language_id, or context_id
        if ($request->has('theme_id')) {
            $query->where('theme_id', $request->theme_id);
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

        return ThemeTranslationResource::collection($translations);
    }

    /**
     * Store a newly created theme translation
     *
     * @response ThemeTranslationResource
     */
    public function store(StoreThemeTranslationRequest $request)
    {
        try {
            $translation = ThemeTranslation::create($request->validated());

            return new ThemeTranslationResource($translation);
        } catch (QueryException $e) {
            // Handle integrity constraint violations (SQLSTATE 23000 is database-agnostic)
            if ($e->getCode() === '23000') {
                return response()->json([
                    'message' => 'A translation for this theme, language and context combination already exists.',
                    'errors' => [
                        'theme_id' => ['A translation for this theme, language and context combination already exists.'],
                    ],
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
            throw $e;
        }
    }

    /**
     * Display the specified theme translation
     *
     * @response ThemeTranslationResource
     */
    public function show(ThemeTranslation $themeTranslation)
    {
        return new ThemeTranslationResource($themeTranslation);
    }

    /**
     * Update the specified theme translation
     *
     * @response ThemeTranslationResource
     */
    public function update(UpdateThemeTranslationRequest $request, ThemeTranslation $themeTranslation)
    {
        try {
            $themeTranslation->update($request->validated());

            return new ThemeTranslationResource($themeTranslation);
        } catch (QueryException $e) {
            // Handle integrity constraint violations (SQLSTATE 23000 is database-agnostic)
            if ($e->getCode() === '23000') {
                return response()->json([
                    'message' => 'A translation for this theme, language and context combination already exists.',
                    'errors' => [
                        'theme_id' => ['A translation for this theme, language and context combination already exists.'],
                    ],
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
            throw $e;
        }
    }

    /**
     * Remove the specified theme translation
     *
     * @response 204
     */
    public function destroy(ThemeTranslation $themeTranslation)
    {
        $themeTranslation->delete();

        return response()->noContent();
    }
}
