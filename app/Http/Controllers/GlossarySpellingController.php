<?php

namespace App\Http\Controllers;

use App\Http\Requests\Api\IndexGlossarySpellingRequest;
use App\Http\Requests\Api\ShowGlossarySpellingRequest;
use App\Http\Requests\Api\StoreGlossarySpellingRequest;
use App\Http\Requests\Api\UpdateGlossarySpellingRequest;
use App\Http\Resources\GlossarySpellingResource;
use App\Models\GlossarySpelling;

class GlossarySpellingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(IndexGlossarySpellingRequest $request)
    {
        $includes = $request->getIncludeParams();
        $pagination = $request->getPaginationParams();

        $query = GlossarySpelling::query()->with($includes);
        $paginator = $query->paginate(
            $pagination['per_page'],
            ['*'],
            'page',
            $pagination['page']
        );

        return GlossarySpellingResource::collection($paginator);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return GlossarySpellingResource
     */
    public function store(StoreGlossarySpellingRequest $request)
    {
        $validated = $request->validated();
        $spelling = GlossarySpelling::create($validated);
        $spelling->refresh();

        return new GlossarySpellingResource($spelling);
    }

    /**
     * Display the specified resource.
     */
    public function show(ShowGlossarySpellingRequest $request, GlossarySpelling $glossarySpelling)
    {
        $includes = $request->getIncludeParams();
        if (! empty($includes)) {
            $glossarySpelling->load($includes);
        }

        return new GlossarySpellingResource($glossarySpelling);
    }

    /**
     * Update the specified resource in storage.
     *
     * @return GlossarySpellingResource
     */
    public function update(UpdateGlossarySpellingRequest $request, GlossarySpelling $glossarySpelling)
    {
        $validated = $request->validated();
        $glossarySpelling->update($validated);
        $glossarySpelling->refresh();

        return new GlossarySpellingResource($glossarySpelling);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(GlossarySpelling $glossarySpelling)
    {
        $glossarySpelling->delete();

        return response()->noContent();
    }
}
