<?php

namespace App\Http\Controllers;

use App\Http\Requests\Api\AttachGlossarySynonymRequest;
use App\Http\Requests\Api\DetachGlossarySynonymRequest;
use App\Http\Requests\Api\IndexGlossaryRequest;
use App\Http\Requests\Api\ShowGlossaryRequest;
use App\Http\Requests\Api\StoreGlossaryRequest;
use App\Http\Requests\Api\UpdateGlossaryRequest;
use App\Http\Resources\GlossaryResource;
use App\Models\Glossary;

class GlossaryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(IndexGlossaryRequest $request)
    {
        $includes = $request->getIncludeParams();
        $pagination = $request->getPaginationParams();

        $query = Glossary::query()->with($includes);
        $paginator = $query->paginate(
            $pagination['per_page'],
            ['*'],
            'page',
            $pagination['page']
        );

        return GlossaryResource::collection($paginator);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return GlossaryResource
     */
    public function store(StoreGlossaryRequest $request)
    {
        $validated = $request->validated();
        $glossary = Glossary::create($validated);
        $glossary->refresh();

        return new GlossaryResource($glossary);
    }

    /**
     * Display the specified resource.
     */
    public function show(ShowGlossaryRequest $request, Glossary $glossary)
    {
        $includes = $request->getIncludeParams();
        if (! empty($includes)) {
            $glossary->load($includes);
        }

        return new GlossaryResource($glossary);
    }

    /**
     * Update the specified resource in storage.
     *
     * @return GlossaryResource
     */
    public function update(UpdateGlossaryRequest $request, Glossary $glossary)
    {
        $validated = $request->validated();
        $glossary->update($validated);
        $glossary->refresh();

        return new GlossaryResource($glossary);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Glossary $glossary)
    {
        $glossary->delete();

        return response()->noContent();
    }

    /**
     * Attach a synonym to the glossary entry.
     *
     * @return GlossaryResource
     */
    public function attachSynonym(AttachGlossarySynonymRequest $request, Glossary $glossary)
    {
        $validated = $request->validated();
        $synonymId = $validated['synonym_id'];

        // Prevent self-reference
        if ($glossary->id === $synonymId) {
            return response()->json([
                'message' => 'A glossary entry cannot be a synonym of itself.',
            ], 422);
        }

        // Attach the synonym if not already attached
        if (! $glossary->synonyms()->where('synonym_id', $synonymId)->exists()) {
            $glossary->synonyms()->attach($synonymId);
        }

        $glossary->load('synonyms');

        return new GlossaryResource($glossary);
    }

    /**
     * Detach a synonym from the glossary entry.
     *
     * @return GlossaryResource
     */
    public function detachSynonym(DetachGlossarySynonymRequest $request, Glossary $glossary)
    {
        $validated = $request->validated();
        $synonymId = $validated['synonym_id'];

        $glossary->synonyms()->detach($synonymId);
        $glossary->load('synonyms');

        return new GlossaryResource($glossary);
    }
}
