<?php

namespace App\Http\Controllers;

use App\Http\Requests\Api\IndexGlossaryTranslationRequest;
use App\Http\Requests\Api\ShowGlossaryTranslationRequest;
use App\Http\Requests\Api\StoreGlossaryTranslationRequest;
use App\Http\Requests\Api\UpdateGlossaryTranslationRequest;
use App\Http\Resources\GlossaryTranslationResource;
use App\Models\GlossaryTranslation;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class GlossaryTranslationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(IndexGlossaryTranslationRequest $request): AnonymousResourceCollection
    {
        $includes = $request->getIncludeParams();
        $pagination = $request->getPaginationParams();

        $query = GlossaryTranslation::query()->with($includes);
        $paginator = $query->paginate(
            $pagination['per_page'],
            ['*'],
            'page',
            $pagination['page']
        );

        return GlossaryTranslationResource::collection($paginator);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreGlossaryTranslationRequest $request): GlossaryTranslationResource
    {
        $validated = $request->validated();
        $translation = GlossaryTranslation::create($validated);
        $translation->refresh();

        return new GlossaryTranslationResource($translation);
    }

    /**
     * Display the specified resource.
     */
    public function show(ShowGlossaryTranslationRequest $request, GlossaryTranslation $glossaryTranslation): GlossaryTranslationResource
    {
        $includes = $request->getIncludeParams();
        if (! empty($includes)) {
            $glossaryTranslation->load($includes);
        }

        return new GlossaryTranslationResource($glossaryTranslation);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateGlossaryTranslationRequest $request, GlossaryTranslation $glossaryTranslation): GlossaryTranslationResource
    {
        $validated = $request->validated();
        $glossaryTranslation->update($validated);
        $glossaryTranslation->refresh();

        return new GlossaryTranslationResource($glossaryTranslation);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(GlossaryTranslation $glossaryTranslation): Response
    {
        $glossaryTranslation->delete();

        return response()->noContent();
    }
}
