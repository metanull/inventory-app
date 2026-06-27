<?php

namespace App\Http\Controllers;

use App\Http\Requests\Api\IndexContributorRequest;
use App\Http\Requests\Api\ShowContributorRequest;
use App\Http\Requests\Api\StoreContributorRequest;
use App\Http\Requests\Api\UpdateContributorRequest;
use App\Http\Resources\ContributorResource;
use App\Models\Contributor;
use App\Support\Includes\AllowList;
use App\Support\Includes\IncludeParser;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class ContributorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(IndexContributorRequest $request): AnonymousResourceCollection
    {
        $includes = $request->getIncludeParams();
        $pagination = $request->getPaginationParams();

        $query = Contributor::query()->with($includes);
        $paginator = $query->paginate(
            $pagination['per_page'],
            ['*'],
            'page',
            $pagination['page']
        );

        return ContributorResource::collection($paginator);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreContributorRequest $request): ContributorResource
    {
        $validated = $request->validated();
        $contributor = Contributor::create($validated);
        $contributor->refresh();
        $includes = IncludeParser::fromRequest($request, AllowList::for('contributor'));
        $contributor->load($includes);

        return new ContributorResource($contributor);
    }

    /**
     * Display the specified resource.
     */
    public function show(ShowContributorRequest $request, Contributor $contributor): ContributorResource
    {
        $includes = $request->getIncludeParams();
        $contributor->load($includes);

        return new ContributorResource($contributor);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateContributorRequest $request, Contributor $contributor): ContributorResource
    {
        $validated = $request->validated();
        $contributor->update($validated);
        $contributor->refresh();
        $includes = IncludeParser::fromRequest($request, AllowList::for('contributor'));
        $contributor->load($includes);

        return new ContributorResource($contributor);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Contributor $contributor): Response
    {
        $contributor->delete();

        return response()->noContent();
    }
}
