<?php

namespace App\Http\Controllers;

use App\Http\Resources\ThemeResource;
use App\Models\Theme;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ThemeController extends Controller
{
    /**
     * Display a listing of the themes for an exhibition.
     */
    public function index(Request $request)
    {
        $themes = Theme::with(['translations', 'subthemes.translations'])
            ->whereNull('parent_id')
            ->paginate(15);

        return ThemeResource::collection($themes);
    }

    /**
     * Store a newly created theme in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'exhibition_id' => ['required', 'uuid', 'exists:exhibitions,id'],
            'parent_id' => ['nullable', 'uuid', 'exists:themes,id'],
            'internal_name' => ['required', 'string'],
            'backward_compatibility' => ['nullable', 'string'],
        ]);
        $theme = Theme::create($validated);

        return new ThemeResource($theme->load(['translations', 'subthemes.translations']));
    }

    /**
     * Display the specified theme.
     */
    public function show(Theme $theme)
    {
        $theme->load(['translations', 'subthemes.translations']);

        return new ThemeResource($theme);
    }

    /**
     * Update the specified theme in storage.
     */
    public function update(Request $request, Theme $theme)
    {
        $validated = $request->validate([
            'internal_name' => ['sometimes', 'string', Rule::unique('themes', 'internal_name')->ignore($theme->id, 'id')],
            'backward_compatibility' => ['nullable', 'string'],
        ]);
        $theme->update($validated);

        return new ThemeResource($theme->load(['translations', 'subthemes.translations']));
    }

    /**
     * Remove the specified theme from storage.
     */
    public function destroy(Theme $theme)
    {
        $theme->delete();

        return response()->noContent();
    }
}
