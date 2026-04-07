<?php

namespace App\Http\Controllers;

use App\Http\Requests\Api\IndexTimelineRequest;
use App\Http\Requests\Api\ShowTimelineRequest;
use App\Http\Requests\Api\StoreTimelineRequest;
use App\Http\Requests\Api\UpdateTimelineRequest;
use App\Http\Resources\TimelineResource;
use App\Models\Timeline;
use App\Support\Includes\AllowList;
use App\Support\Includes\IncludeParser;

class TimelineController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(IndexTimelineRequest $request)
    {
        $includes = $request->getIncludeParams();
        $pagination = $request->getPaginationParams();

        $query = Timeline::query()->with($includes);
        $paginator = $query->paginate(
            $pagination['per_page'],
            ['*'],
            'page',
            $pagination['page']
        );

        return TimelineResource::collection($paginator);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTimelineRequest $request)
    {
        $validated = $request->validated();
        $timeline = Timeline::create($validated);
        $timeline->refresh();
        $includes = IncludeParser::fromRequest($request, AllowList::for('timeline'));
        $timeline->load($includes);

        return new TimelineResource($timeline);
    }

    /**
     * Display the specified resource.
     */
    public function show(ShowTimelineRequest $request, Timeline $timeline)
    {
        $includes = $request->getIncludeParams();
        $timeline->load($includes);

        return new TimelineResource($timeline);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTimelineRequest $request, Timeline $timeline)
    {
        $validated = $request->validated();
        $timeline->update($validated);
        $timeline->refresh();
        $includes = IncludeParser::fromRequest($request, AllowList::for('timeline'));
        $timeline->load($includes);

        return new TimelineResource($timeline);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Timeline $timeline)
    {
        $timeline->delete();

        return response()->noContent();
    }
}
