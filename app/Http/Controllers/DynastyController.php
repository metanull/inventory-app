<?php

namespace App\Http\Controllers;

use App\Http\Requests\Api\IndexDynastyRequest;
use App\Http\Requests\Api\ShowDynastyRequest;
use App\Http\Requests\Api\StoreDynastyRequest;
use App\Http\Requests\Api\UpdateDynastyRequest;
use App\Http\Resources\DynastyResource;
use App\Models\Dynasty;
use App\Support\Includes\AllowList;
use App\Support\Includes\IncludeParser;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class DynastyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(IndexDynastyRequest $request): AnonymousResourceCollection
    {
        $includes = $request->getIncludeParams();
        $pagination = $request->getPaginationParams();

        $query = Dynasty::query()->with($includes);
        $paginator = $query->paginate(
            $pagination['per_page'],
            ['*'],
            'page',
            $pagination['page']
        );

        return DynastyResource::collection($paginator);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDynastyRequest $request): DynastyResource
    {
        $validated = $request->validated();
        $dynasty = Dynasty::create($validated);
        $dynasty->refresh();
        $includes = IncludeParser::fromRequest($request, AllowList::for('dynasty'));
        $dynasty->load($includes);

        return new DynastyResource($dynasty);
    }

    /**
     * Display the specified resource.
     */
    public function show(ShowDynastyRequest $request, Dynasty $dynasty): DynastyResource
    {
        $includes = $request->getIncludeParams();
        $dynasty->load($includes);

        return new DynastyResource($dynasty);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDynastyRequest $request, Dynasty $dynasty): DynastyResource
    {
        $validated = $request->validated();
        $dynasty->update($validated);
        $dynasty->refresh();
        $includes = IncludeParser::fromRequest($request, AllowList::for('dynasty'));
        $dynasty->load($includes);

        return new DynastyResource($dynasty);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Dynasty $dynasty): Response
    {
        $dynasty->delete();

        return response()->noContent();
    }
}
