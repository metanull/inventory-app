<?php

namespace App\Http\Controllers;

use App\Http\Requests\Api\StoreTimelineEventTranslationRequest;
use App\Http\Requests\Api\UpdateTimelineEventTranslationRequest;
use App\Http\Resources\TimelineEventTranslationResource;
use App\Models\TimelineEventTranslation;
use App\Support\Includes\AllowList;
use App\Support\Includes\IncludeParser;
use Illuminate\Http\Response;

class TimelineEventTranslationController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTimelineEventTranslationRequest $request): TimelineEventTranslationResource
    {
        $validated = $request->validated();
        $translation = TimelineEventTranslation::create($validated);
        $translation->refresh();
        $includes = IncludeParser::fromRequest($request, AllowList::for('timeline_event_translation'));
        $translation->load($includes);

        return new TimelineEventTranslationResource($translation);
    }

    /**
     * Display the specified resource.
     */
    public function show(TimelineEventTranslation $timelineEventTranslation): TimelineEventTranslationResource
    {
        return new TimelineEventTranslationResource($timelineEventTranslation);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTimelineEventTranslationRequest $request, TimelineEventTranslation $timelineEventTranslation): TimelineEventTranslationResource
    {
        $validated = $request->validated();
        $timelineEventTranslation->update($validated);
        $timelineEventTranslation->refresh();
        $includes = IncludeParser::fromRequest($request, AllowList::for('timeline_event_translation'));
        $timelineEventTranslation->load($includes);

        return new TimelineEventTranslationResource($timelineEventTranslation);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TimelineEventTranslation $timelineEventTranslation): Response
    {
        $timelineEventTranslation->delete();

        return response()->noContent();
    }
}
