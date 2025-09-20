<?php

namespace App\Http\Controllers;

use App\Http\Requests\Partner\IndexPartnerRequest;
use App\Http\Requests\Partner\ShowPartnerRequest;
use App\Http\Requests\Partner\StorePartnerRequest;
use App\Http\Requests\Partner\UpdatePartnerRequest;
use App\Http\Resources\PartnerResource;
use App\Models\Partner;
use App\Support\Includes\AllowList;
use App\Support\Includes\IncludeParser;
use App\Support\Pagination\PaginationParams;

class PartnerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(IndexPartnerRequest $request)
    {
        $includes = IncludeParser::fromRequest($request, AllowList::for('partner'));
        $pagination = PaginationParams::fromRequest($request);

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
     */
    public function store(StorePartnerRequest $request)
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
    public function show(ShowPartnerRequest $request, Partner $partner)
    {
        $includes = IncludeParser::fromRequest($request, AllowList::for('partner'));
        $partner->load($includes);

        return new PartnerResource($partner);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePartnerRequest $request, Partner $partner)
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
    public function destroy(Partner $partner)
    {
        $partner->delete();

        return response()->noContent();
    }
}
