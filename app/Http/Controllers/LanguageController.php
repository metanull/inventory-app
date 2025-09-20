<?php

namespace App\Http\Controllers;

use App\Http\Requests\Language\IndexLanguageRequest;
use App\Http\Requests\Language\SetDefaultLanguageRequest;
use App\Http\Requests\Language\ShowLanguageRequest;
use App\Http\Requests\Language\StoreLanguageRequest;
use App\Http\Requests\Language\UpdateLanguageRequest;
use App\Http\Resources\LanguageResource;
use App\Models\Language;
use App\Support\Pagination\PaginationParams;

class LanguageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(IndexLanguageRequest $request)
    {
        $pagination = PaginationParams::fromRequest($request);

        $query = Language::query();
        $paginator = $query->paginate(
            $pagination['per_page'],
            ['*'],
            'page',
            $pagination['page']
        );

        return LanguageResource::collection($paginator);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreLanguageRequest $request)
    {
        $validated = $request->validated();
        $language = Language::create($validated);
        $language->refresh();

        return new LanguageResource($language);
    }

    /**
     * Display the specified resource.
     */
    public function show(ShowLanguageRequest $request, Language $language)
    {
        return new LanguageResource($language);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateLanguageRequest $request, Language $language)
    {
        $validated = $request->validated();
        $language->update($validated);
        $language->refresh();

        return new LanguageResource($language);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Language $language)
    {
        $language->delete();

        return response()->noContent();
    }

    /**
     * Set or unset a Language as the default one.
     */
    public function setDefault(SetDefaultLanguageRequest $request, Language $language)
    {
        $validated = $request->validated();

        if ($validated['is_default'] === true) {
            $language->setDefault();
        } else {
            $language->unsetDefault();
        }
        $language->refresh();

        return new LanguageResource($language);
    }

    /**
     * Clear the default flag from any language.
     */
    public function clearDefault()
    {
        Language::clearDefault();

        return response()->json(['message' => 'Default language cleared']);
    }

    /**
     * Get the default Language.
     */
    public function getDefault()
    {
        $language = Language::default()->first();
        if (! $language) {
            return response()->json(['message' => 'No default language found'], 404);
        }

        return new LanguageResource($language);
    }

    /**
     * Get the english Language.
     */
    public function getEnglish()
    {
        $language = Language::english()->first();
        if (! $language) {
            return response()->json(['message' => 'No English language found'], 404);
        }

        return new LanguageResource($language);
    }
}
