<?php

namespace App\Http\Controllers;

use App\Http\Requests\Api\IndexThemeTranslationRequest;
use App\Http\Requests\Api\ShowThemeTranslationRequest;
use App\Http\Requests\Api\StoreThemeTranslationRequest;
use App\Http\Requests\Api\UpdateThemeTranslationRequest;
use App\Http\Resources\ThemeTranslationResource;
use App\Models\ThemeTranslation;

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
     * @return ThemeTranslationResource
     */
    public function store(StoreThemeTranslationRequest $request)
    {
        $validated = $request->validated();
        $translation = ThemeTranslation::create($validated);
        $translation->refresh();

        return new ThemeTranslationResource($translation);
    }

    /**
     * Display the specified theme translation
     */
    public function show(ShowThemeTranslationRequest $request, ThemeTranslation $themeTranslation)
    {
        $includes = $request->getIncludeParams();
        if (! empty($includes)) {
            $themeTranslation->load($includes);
        }

        return new ThemeTranslationResource($themeTranslation);
    }

    /**
     * Update the specified theme translation
     *
     * @return ThemeTranslationResource
     */
    public function update(UpdateThemeTranslationRequest $request, ThemeTranslation $themeTranslation)
    {
        $validated = $request->validated();
        $themeTranslation->update($validated);
        $themeTranslation->refresh();

        return new ThemeTranslationResource($themeTranslation);
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
