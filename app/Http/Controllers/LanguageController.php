<?php

namespace App\Http\Controllers;

use App\Http\Resources\LanguageResource;
use App\Models\Language;
use Illuminate\Http\Request;

class LanguageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return LanguageResource::collection(Language::all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|string|size:3',
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
            'internal_name' => 'string',
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

        return response()->json(null, 204);
    }

    /**
     * Set a Language as the default one.
     */
    public function setDefault(Request $request, Language $context)
    {
        $validated = $request->validate([
            'is_default' => 'required|boolean',
        ]);

        // Ensure only one context can be default
        if ($validated['is_default']) {
            Language::where('is_default', true)->update(['is_default' => false]);
        }

        $context->update($validated);
        $context->refresh();

        return new LanguageResource($context);
    }

    /**
     * Get the default Language.
     */
    public function getDefault()
    {
        $context = Language::where('is_default', true)->first();

        if (! $context) {
            return response()->json(['message' => 'No default context found'], 404);
        }

        return new LanguageResource($context);
    }
}
