<?php

namespace App\Http\Controllers;

use App\Http\Requests\Api\IndexPartnerRequest;
use App\Http\Requests\Api\ShowPartnerRequest;
use App\Http\Requests\Api\StorePartnerRequest;
use App\Http\Requests\Api\UpdatePartnerRequest;
use App\Http\Resources\PartnerResource;
use App\Models\Partner;
use App\Support\Includes\AllowList;
use App\Support\Includes\IncludeParser;

class PartnerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(IndexPartnerRequest $request): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        $includes = $request->getIncludeParams();
        $pagination = $request->getPaginationParams();

        $query = Partner::query()->with($includes);
        $paginator = $query->paginate(
            $pagination['per_page'],
            ['*'],
            'page',
            $pagination['page']
        );

        return PartnerResource::collection($paginator);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return PartnerResource
     */
    public function store(StorePartnerRequest $request): PartnerResource
    {
        $validated = $request->validated();
        $partner = Partner::create($validated);
        $partner->refresh();
        $includes = IncludeParser::fromRequest($request, AllowList::for('partner'));
        $partner->load($includes);

        return new PartnerResource($partner);
    }

    /**
     * Display the specified resource.
     */
    public function show(ShowPartnerRequest $request, Partner $partner): PartnerResource
    {
        $includes = $request->getIncludeParams();
        $partner->load($includes);

        return new PartnerResource($partner);
    }

    /**
     * Update the specified resource in storage.
     *
     * @return PartnerResource
     */
    public function update(UpdatePartnerRequest $request, Partner $partner): PartnerResource
    {
        $validated = $request->validated();
        $partner->update($validated);
        $partner->refresh();
        $includes = IncludeParser::fromRequest($request, AllowList::for('partner'));
        $partner->load($includes);

        return new PartnerResource($partner);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Partner $partner): \Illuminate\Http\Response
    {
        $partner->delete();

        return response()->noContent();
    }
}
