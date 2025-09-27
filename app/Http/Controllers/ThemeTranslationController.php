<?php

namespace App\Http\Controllers;

use App\Http\Requests\Api\IndexThemeTranslationRequest;
use App\Http\Resources\ThemeTranslationResource;
use App\Models\ThemeTranslation;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
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
    public function store(Request $request)
    {
        $validated = $request->validate([
            'theme_id' => 'required|uuid|exists:themes,id',
            'language_id' => 'required|string|exists:languages,id',
            'context_id' => 'required|uuid|exists:contexts,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'introduction' => 'required|string',
            'backward_compatibility' => 'nullable|string|max:255',
            'extra' => 'nullable|json',
        ]);

        try {
            $translation = ThemeTranslation::create($validated);

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
    public function update(Request $request, ThemeTranslation $themeTranslation)
    {
        $validated = $request->validate([
            'theme_id' => 'sometimes|required|uuid|exists:themes,id',
            'language_id' => 'sometimes|required|string|exists:languages,id',
            'context_id' => 'sometimes|required|uuid|exists:contexts,id',
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'introduction' => 'sometimes|required|string',
            'backward_compatibility' => 'nullable|string|max:255',
            'extra' => 'nullable|json',
        ]);

        try {
            $themeTranslation->update($validated);

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
