<?php

namespace App\Http\Controllers;

use App\Http\Resources\PictureTranslationResource;
use App\Models\PictureTranslation;
use Illuminate\Http\Request;

class PictureTranslationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $pictureTranslations = PictureTranslation::paginate();

        return PictureTranslationResource::collection($pictureTranslations);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'picture_id' => 'required|uuid|exists:pictures,id',
            'language_id' => 'required|string|max:3|exists:languages,id',
            'context_id' => 'required|uuid|exists:contexts,id',
            'description' => 'required|string',
            'caption' => 'required|string',
            'author_id' => 'nullable|uuid|exists:authors,id',
            'text_copy_editor_id' => 'nullable|uuid|exists:authors,id',
            'translator_id' => 'nullable|uuid|exists:authors,id',
            'translation_copy_editor_id' => 'nullable|uuid|exists:authors,id',
            'backward_compatibility' => 'nullable|string',
            'extra' => 'nullable|array',
        ]);

        // Validate unique constraint
        $uniqueValidation = $request->validate([
            'picture_id' => [
                'required',
                'uuid',
                'exists:pictures,id',
                function ($attribute, $value, $fail) use ($request) {
                    $exists = PictureTranslation::where('picture_id', $value)
                        ->where('language_id', $request->language_id)
                        ->where('context_id', $request->context_id)
                        ->exists();

                    if ($exists) {
                        $fail('A translation for this picture, language and context combination already exists.');
                    }
                },
            ],
        ]);

        $pictureTranslation = PictureTranslation::create($validated);

        return new PictureTranslationResource($pictureTranslation);
    }

    /**
     * Display the specified resource.
     */
    public function show(PictureTranslation $pictureTranslation)
    {
        return new PictureTranslationResource($pictureTranslation);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PictureTranslation $pictureTranslation)
    {
        $validated = $request->validate([
            'picture_id' => 'sometimes|uuid|exists:pictures,id',
            'language_id' => 'sometimes|string|max:3|exists:languages,id',
            'context_id' => 'sometimes|uuid|exists:contexts,id',
            'description' => 'sometimes|string',
            'caption' => 'sometimes|string',
            'author_id' => 'nullable|uuid|exists:authors,id',
            'text_copy_editor_id' => 'nullable|uuid|exists:authors,id',
            'translator_id' => 'nullable|uuid|exists:authors,id',
            'translation_copy_editor_id' => 'nullable|uuid|exists:authors,id',
            'backward_compatibility' => 'nullable|string',
            'extra' => 'nullable|array',
        ]);

        // Check if any of the unique constraint fields are being updated
        if ($request->filled('picture_id') || $request->filled('language_id') || $request->filled('context_id')) {
            // Get the new values or use current values if not changing
            $pictureId = $request->filled('picture_id') ? $request->picture_id : $pictureTranslation->picture_id;
            $languageId = $request->filled('language_id') ? $request->language_id : $pictureTranslation->language_id;
            $contextId = $request->filled('context_id') ? $request->context_id : $pictureTranslation->context_id;

            // Validate that the new combination doesn't already exist
            $request->validate([
                'picture_id' => [
                    'sometimes',
                    'uuid',
                    'exists:pictures,id',
                    function ($attribute, $value, $fail) use ($pictureId, $languageId, $contextId, $pictureTranslation) {
                        $exists = PictureTranslation::where('picture_id', $pictureId)
                            ->where('language_id', $languageId)
                            ->where('context_id', $contextId)
                            ->where('id', '!=', $pictureTranslation->id) // Exclude current record
                            ->exists();

                        if ($exists) {
                            $fail('A translation for this picture, language and context combination already exists.');
                        }
                    },
                ],
            ]);
        }

        $pictureTranslation->update($validated);

        return new PictureTranslationResource($pictureTranslation);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PictureTranslation $pictureTranslation)
    {
        $pictureTranslation->delete();

        return response()->noContent();
    }
}
