<?php

namespace App\Http\Controllers;

use App\Http\Requests\Api\IndexTimelineEventRequest;
use App\Http\Requests\Api\ShowTimelineEventRequest;
use App\Http\Requests\Api\StoreTimelineEventRequest;
use App\Http\Requests\Api\UpdateTimelineEventRequest;
use App\Http\Resources\TimelineEventResource;
use App\Models\TimelineEvent;
use App\Support\Includes\AllowList;
use App\Support\Includes\IncludeParser;

class TimelineEventController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(IndexTimelineEventRequest $request)
    {
        $includes = $request->getIncludeParams();
        $pagination = $request->getPaginationParams();

        $query = TimelineEvent::query()->with($includes);
        $paginator = $query->paginate(
            $pagination['per_page'],
            ['*'],
            'page',
            $pagination['page']
        );

        return TimelineEventResource::collection($paginator);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTimelineEventRequest $request)
    {
        $validated = $request->validated();
        $event = TimelineEvent::create($validated);
        $event->refresh();
        $includes = IncludeParser::fromRequest($request, AllowList::for('timeline_event'));
        $event->load($includes);

        return new TimelineEventResource($event);
    }

    /**
     * Display the specified resource.
     */
    public function show(ShowTimelineEventRequest $request, TimelineEvent $timelineEvent)
    {
        $includes = $request->getIncludeParams();
        $timelineEvent->load($includes);

        return new TimelineEventResource($timelineEvent);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTimelineEventRequest $request, TimelineEvent $timelineEvent)
    {
        $validated = $request->validated();
        $timelineEvent->update($validated);
        $timelineEvent->refresh();
        $includes = IncludeParser::fromRequest($request, AllowList::for('timeline_event'));
        $timelineEvent->load($includes);

        return new TimelineEventResource($timelineEvent);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TimelineEvent $timelineEvent)
    {
        $timelineEvent->delete();

        return response()->noContent();
    }
}
