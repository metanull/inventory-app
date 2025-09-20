<?php

namespace App\Http\Controllers;

use App\Http\Requests\Theme\DestroyThemeRequest;
use App\Http\Requests\Theme\IndexThemeRequest;
use App\Http\Requests\Theme\ShowThemeRequest;
use App\Http\Requests\Theme\StoreThemeRequest;
use App\Http\Requests\Theme\UpdateThemeRequest;
use App\Http\Resources\ThemeResource;
use App\Models\Theme;
use App\Support\Includes\AllowList;
use App\Support\Includes\IncludeParser;
use App\Support\Pagination\PaginationParams;

class ThemeController extends Controller
{
    /**
     * Display a listing of the themes for an exhibition.
     */
    public function index(IndexThemeRequest $request)
    {
        $validatedData = $request->validated();
        $includes = IncludeParser::fromRequest($request, AllowList::for('theme'));
        $pagination = PaginationParams::fromRequest($request);

        $query = Theme::query()->whereNull('parent_id');
        if (! empty($includes)) {
            $query->with($includes);
        }

        $paginator = $query->paginate(
            $pagination['per_page'],
            ['*'],
            'page',
            $pagination['page']
        );

        return ThemeResource::collection($paginator);
    }

    /**
     * Store a newly created theme in storage.
     */
    public function store(StoreThemeRequest $request)
    {
        $validated = $request->validated();
        $theme = Theme::create($validated);
        $requested = IncludeParser::fromRequest($request, AllowList::for('theme'));
        if (! empty($requested)) {
            $theme->load($requested);
        }

        return new ThemeResource($theme);
    }

    /**
     * Display the specified theme.
     */
    public function show(ShowThemeRequest $request, Theme $theme)
    {
        $validatedData = $request->validated();
        $includes = IncludeParser::fromRequest($request, AllowList::for('theme'));
        if (! empty($includes)) {
            $theme->load($includes);
        }

        return new ThemeResource($theme);
    }

    /**
     * Update the specified theme in storage.
     */
    public function update(UpdateThemeRequest $request, Theme $theme)
    {
        $validated = $request->validated();
        $theme->update($validated);
        $requested = IncludeParser::fromRequest($request, AllowList::for('theme'));
        if (! empty($requested)) {
            $theme->load($requested);
        }

        return new ThemeResource($theme);
    }

    /**
     * Remove the specified theme from storage.
     */
    public function destroy(DestroyThemeRequest $request, Theme $theme)
    {
        $validatedData = $request->validated();
        $theme->delete();

        return response()->noContent();
    }
}
