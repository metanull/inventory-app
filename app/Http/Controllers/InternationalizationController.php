<?php

namespace App\Http\Controllers;

use App\Http\Resources\InternationalizationResource;
use App\Models\Internationalization;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class InternationalizationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): AnonymousResourceCollection
    {
        $internationalizations = Internationalization::with(['contextualization', 'language', 'author', 'textCopyEditor', 'translator', 'translationCopyEditor'])->paginate();

        return InternationalizationResource::collection($internationalizations);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            /** @ignoreParam */
            'id' => 'prohibited',
            'contextualization_id' => 'required|uuid|exists:contextualizations,id',
            'language_id' => 'required|string|size:3|exists:languages,id',
            'name' => 'required|string',
            'alternate_name' => 'nullable|string',
            'description' => 'required|string',
            'type' => 'nullable|string',
            'holder' => 'nullable|string',
            'owner' => 'nullable|string',
            'initial_owner' => 'nullable|string',
            'dates' => 'nullable|string',
            'location' => 'nullable|string',
            'dimensions' => 'nullable|string',
            'place_of_production' => 'nullable|string',
            'method_for_datation' => 'nullable|string',
            'method_for_provenance' => 'nullable|string',
            'obtention' => 'nullable|string',
            'bibliography' => 'nullable|string',
            'extra' => 'nullable|array',
            'author_id' => 'nullable|uuid|exists:authors,id',
            'text_copy_editor_id' => 'nullable|uuid|exists:authors,id',
            'translator_id' => 'nullable|uuid|exists:authors,id',
            'translation_copy_editor_id' => 'nullable|uuid|exists:authors,id',
            'backward_compatibility' => 'nullable|string',
        ]);

        try {
            $internationalization = Internationalization::create($validated);
            $internationalization->refresh();
            $internationalization->load(['contextualization', 'language', 'author', 'textCopyEditor', 'translator', 'translationCopyEditor']);

            return (new InternationalizationResource($internationalization))->response()->setStatusCode(201);
        } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => [
                    'contextualization_id' => ['The combination of contextualization and language must be unique.'],
                    'language_id' => ['The combination of contextualization and language must be unique.'],
                ],
            ], 422);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Internationalization $internationalization): InternationalizationResource
    {
        $internationalization->load(['contextualization', 'language', 'author', 'textCopyEditor', 'translator', 'translationCopyEditor']);

        return new InternationalizationResource($internationalization);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Internationalization $internationalization)
    {
        $validated = $request->validate([
            /** @ignoreParam */
            'id' => 'prohibited',
            'contextualization_id' => 'sometimes|uuid|exists:contextualizations,id',
            'language_id' => [
                'sometimes',
                'string',
                'size:3',
                'exists:languages,id',
                Rule::unique('internationalizations', 'language_id')
                    ->where('contextualization_id', $request->contextualization_id ?? $internationalization->contextualization_id)
                    ->ignore($internationalization->id),
            ],
            'name' => 'sometimes|string',
            'alternate_name' => 'nullable|string',
            'description' => 'sometimes|string',
            'type' => 'nullable|string',
            'holder' => 'nullable|string',
            'owner' => 'nullable|string',
            'initial_owner' => 'nullable|string',
            'dates' => 'nullable|string',
            'location' => 'nullable|string',
            'dimensions' => 'nullable|string',
            'place_of_production' => 'nullable|string',
            'method_for_datation' => 'nullable|string',
            'method_for_provenance' => 'nullable|string',
            'obtention' => 'nullable|string',
            'bibliography' => 'nullable|string',
            'extra' => 'nullable|array',
            'author_id' => 'nullable|uuid|exists:authors,id',
            'text_copy_editor_id' => 'nullable|uuid|exists:authors,id',
            'translator_id' => 'nullable|uuid|exists:authors,id',
            'translation_copy_editor_id' => 'nullable|uuid|exists:authors,id',
            'backward_compatibility' => 'nullable|string',
        ]);

        $internationalization->update($validated);
        $internationalization->refresh();
        $internationalization->load(['contextualization', 'language', 'author', 'textCopyEditor', 'translator', 'translationCopyEditor']);

        return new InternationalizationResource($internationalization);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Internationalization $internationalization)
    {
        $internationalization->delete();

        return response()->noContent();
    }

    /**
     * Display internationalizations in the default language.
     */
    public function inDefaultLanguage(): AnonymousResourceCollection
    {
        $internationalizations = Internationalization::with(['contextualization', 'language', 'author', 'textCopyEditor', 'translator', 'translationCopyEditor'])
            ->inDefaultLanguage()
            ->paginate();

        return InternationalizationResource::collection($internationalizations);
    }

    /**
     * Display internationalizations in English.
     */
    public function inEnglish(): AnonymousResourceCollection
    {
        $internationalizations = Internationalization::with(['contextualization', 'language', 'author', 'textCopyEditor', 'translator', 'translationCopyEditor'])
            ->inEnglish()
            ->paginate();

        return InternationalizationResource::collection($internationalizations);
    }
}
