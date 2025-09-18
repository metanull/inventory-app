<?php

namespace App\Http\Controllers;

use App\Http\Resources\LanguageResource;
use App\Models\Language;
use App\Support\Pagination\PaginationParams;
use Illuminate\Http\Request;

class LanguageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
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
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|string|size:3|unique:languages,id',
            'internal_name' => 'required|string',
            'backward_compatibility' => 'nullable|string|size:2',
            'is_default' => 'prohibited|boolean',
        ]);
        $language = Language::create($validated);
        $language->refresh();

        return new LanguageResource($language);
    }

    /**
     * Display the specified resource.
     */
    public function show(Language $language)
    {
        return new LanguageResource($language);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Language $language)
    {
        $validated = $request->validate([
            /** @ignoreParam */
            'id' => 'prohibited',
            'internal_name' => 'required|string',
            'backward_compatibility' => 'nullable|string|size:2',
            'is_default' => 'prohibited|boolean',
        ]);
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
    public function setDefault(Request $request, Language $language)
    {
        $validated = $request->validate([
            'is_default' => 'required|boolean',
        ]);

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
