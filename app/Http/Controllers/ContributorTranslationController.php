<?php

namespace App\Http\Controllers;

use App\Http\Requests\Api\IndexContributorTranslationRequest;
use App\Http\Requests\Api\ShowContributorTranslationRequest;
use App\Http\Requests\Api\StoreContributorTranslationRequest;
use App\Http\Requests\Api\UpdateContributorTranslationRequest;
use App\Http\Resources\ContributorTranslationResource;
use App\Models\ContributorTranslation;
use App\Support\Includes\AllowList;
use App\Support\Includes\IncludeParser;

class ContributorTranslationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(IndexContributorTranslationRequest $request): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        $includes = $request->getIncludeParams();
        $pagination = $request->getPaginationParams();

        $query = ContributorTranslation::query()->with($includes);
        $paginator = $query->paginate(
            $pagination['per_page'],
            ['*'],
            'page',
            $pagination['page']
        );

        return ContributorTranslationResource::collection($paginator);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return ContributorTranslationResource
     */
    public function store(StoreContributorTranslationRequest $request): ContributorTranslationResource
    {
        $validated = $request->validated();
        $contributorTranslation = ContributorTranslation::create($validated);
        $contributorTranslation->refresh();
        $includes = IncludeParser::fromRequest($request, AllowList::for('contributor_translation'));
        $contributorTranslation->load($includes);

        return new ContributorTranslationResource($contributorTranslation);
    }

    /**
     * Display the specified resource.
     */
    public function show(ShowContributorTranslationRequest $request, ContributorTranslation $contributorTranslation): ContributorTranslationResource
    {
        $includes = $request->getIncludeParams();
        $contributorTranslation->load($includes);

        return new ContributorTranslationResource($contributorTranslation);
    }

    /**
     * Update the specified resource in storage.
     *
     * @return ContributorTranslationResource
     */
    public function update(UpdateContributorTranslationRequest $request, ContributorTranslation $contributorTranslation): ContributorTranslationResource
    {
        $validated = $request->validated();
        $contributorTranslation->update($validated);
        $contributorTranslation->refresh();
        $includes = IncludeParser::fromRequest($request, AllowList::for('contributor_translation'));
        $contributorTranslation->load($includes);

        return new ContributorTranslationResource($contributorTranslation);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ContributorTranslation $contributorTranslation): \Illuminate\Http\Response
    {
        $contributorTranslation->delete();

        return response()->noContent();
    }
}
