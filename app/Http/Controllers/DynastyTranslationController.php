<?php

namespace App\Http\Controllers;

use App\Http\Requests\Api\IndexDynastyTranslationRequest;
use App\Http\Requests\Api\ShowDynastyTranslationRequest;
use App\Http\Requests\Api\StoreDynastyTranslationRequest;
use App\Http\Requests\Api\UpdateDynastyTranslationRequest;
use App\Http\Resources\DynastyTranslationResource;
use App\Models\DynastyTranslation;
use App\Support\Includes\AllowList;
use App\Support\Includes\IncludeParser;

class DynastyTranslationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(IndexDynastyTranslationRequest $request)
    {
        $includes = $request->getIncludeParams();
        $pagination = $request->getPaginationParams();

        $query = DynastyTranslation::query()->with($includes);
        $paginator = $query->paginate(
            $pagination['per_page'],
            ['*'],
            'page',
            $pagination['page']
        );

        return DynastyTranslationResource::collection($paginator);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return DynastyTranslationResource
     */
    public function store(StoreDynastyTranslationRequest $request)
    {
        $validated = $request->validated();
        $dynastyTranslation = DynastyTranslation::create($validated);
        $dynastyTranslation->refresh();
        $includes = IncludeParser::fromRequest($request, AllowList::for('dynasty_translation'));
        $dynastyTranslation->load($includes);

        return new DynastyTranslationResource($dynastyTranslation);
    }

    /**
     * Display the specified resource.
     */
    public function show(ShowDynastyTranslationRequest $request, DynastyTranslation $dynastyTranslation)
    {
        $includes = $request->getIncludeParams();
        $dynastyTranslation->load($includes);

        return new DynastyTranslationResource($dynastyTranslation);
    }

    /**
     * Update the specified resource in storage.
     *
     * @return DynastyTranslationResource
     */
    public function update(UpdateDynastyTranslationRequest $request, DynastyTranslation $dynastyTranslation)
    {
        $validated = $request->validated();
        $dynastyTranslation->update($validated);
        $dynastyTranslation->refresh();
        $includes = IncludeParser::fromRequest($request, AllowList::for('dynasty_translation'));
        $dynastyTranslation->load($includes);

        return new DynastyTranslationResource($dynastyTranslation);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DynastyTranslation $dynastyTranslation)
    {
        $dynastyTranslation->delete();

        return response()->noContent();
    }
}
