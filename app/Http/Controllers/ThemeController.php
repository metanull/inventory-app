<?php

namespace App\Http\Controllers;

use App\Http\Requests\Api\IndexThemeRequest;
use App\Http\Requests\Api\ShowThemeRequest;
use App\Http\Requests\Api\StoreThemeRequest;
use App\Http\Requests\Api\UpdateThemeRequest;
use App\Http\Resources\ThemeResource;
use App\Models\Theme;
use App\Support\Includes\AllowList;
use App\Support\Includes\IncludeParser;

class ThemeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(IndexThemeRequest $request)
    {
        $includes = $request->getIncludeParams();
        $pagination = $request->getPaginationParams();

        $paginator = Theme::query()->with($includes)->paginate($pagination['per_page'], ['*'], 'page', $pagination['page']);

        return ThemeResource::collection($paginator);
    }

    /**
     * Store a newly created theme in storage.
     *
     * @return ThemeResource
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
     * Display the specified resource.
     */
    public function show(ShowThemeRequest $request, Theme $theme)
    {
        $includes = $request->getIncludeParams();
        $theme->load($includes);

        return new ThemeResource($theme);
    }

    /**
     * Update the specified theme in storage.
     *
     * @return ThemeResource
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
    public function destroy(Theme $theme)
    {
        $theme->delete();

        return response()->noContent();
    }
}
