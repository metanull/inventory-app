<?php

namespace App\Http\Controllers;

use App\Http\Requests\Api\IndexLanguageRequest;
use App\Http\Requests\Api\SetDefaultLanguageRequest;
use App\Http\Requests\Api\ShowLanguageRequest;
use App\Http\Requests\Api\StoreLanguageRequest;
use App\Http\Requests\Api\UpdateLanguageRequest;
use App\Http\Resources\LanguageResource;
use App\Models\Language;

class LanguageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(IndexLanguageRequest $request)
    {
        $pagination = $request->getPaginationParams();

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
     *
     * @return LanguageResource
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
     *
     * @return LanguageResource
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
     *
     * @return LanguageResource
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
