<?php

namespace App\Http\Controllers;

use App\Http\Requests\Detail\IndexDetailRequest;
use App\Http\Requests\Detail\ShowDetailRequest;
use App\Http\Requests\Detail\StoreDetailRequest;
use App\Http\Requests\Detail\UpdateDetailRequest;
use App\Http\Resources\DetailResource;
use App\Models\Detail;
use App\Support\Includes\AllowList;
use App\Support\Includes\IncludeParser;
use App\Support\Pagination\PaginationParams;

class DetailController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(IndexDetailRequest $request)
    {
        $includes = IncludeParser::fromRequest($request, AllowList::for('detail'));
        $pagination = PaginationParams::fromRequest($request);

        $query = Detail::query();
        if (! empty($includes)) {
            $query->with($includes);
        }

        $paginator = $query->paginate($pagination['per_page'], ['*'], 'page', $pagination['page']);

        return DetailResource::collection($paginator);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDetailRequest $request)
    {
        $validated = $request->validated();
        $detail = Detail::create($validated);
        $detail->refresh();
        // Default include 'item' for store response; also honor requested includes
        $requested = IncludeParser::fromRequest($request, AllowList::for('detail'));
        $detail->load(array_values(array_unique(array_merge(['item'], $requested))));

        return new DetailResource($detail);
    }

    /**
     * Display the specified resource.
     */
    public function show(ShowDetailRequest $request, Detail $detail)
    {
        $includes = IncludeParser::fromRequest($request, AllowList::for('detail'));
        if (! empty($includes)) {
            $detail->load($includes);
        }

        return new DetailResource($detail);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDetailRequest $request, Detail $detail)
    {
        $validated = $request->validated();
        $detail->update($validated);
        $detail->refresh();
        // Default include 'item' for update response; also honor requested includes
        $requested = IncludeParser::fromRequest($request, AllowList::for('detail'));
        $detail->load(array_values(array_unique(array_merge(['item'], $requested))));

        return new DetailResource($detail);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Detail $detail)
    {
        $detail->delete();

        return response()->noContent();
    }
}
