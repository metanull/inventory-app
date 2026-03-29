<?php

namespace App\Http\Controllers;

use App\Http\Resources\TimelineEventImageResource;
use App\Models\TimelineEventImage;

class TimelineEventImageController extends Controller
{
    /**
     * Display the specified resource.
     */
    public function show(TimelineEventImage $timelineEventImage)
    {
        return new TimelineEventImageResource($timelineEventImage);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(\Illuminate\Http\Request $request, TimelineEventImage $timelineEventImage)
    {
        $validated = $request->validate([
            'alt_text' => ['nullable', 'string', 'max:255'],
        ]);

        $timelineEventImage->update($validated);
        $timelineEventImage->refresh();

        return new TimelineEventImageResource($timelineEventImage);
    }

    /**
     * Move the image up in display order.
     */
    public function moveUp(TimelineEventImage $timelineEventImage)
    {
        $timelineEventImage->moveUp();

        return new TimelineEventImageResource($timelineEventImage);
    }

    /**
     * Move the image down in display order.
     */
    public function moveDown(TimelineEventImage $timelineEventImage)
    {
        $timelineEventImage->moveDown();

        return new TimelineEventImageResource($timelineEventImage);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TimelineEventImage $timelineEventImage)
    {
        $timelineEventImage->delete();

        return response()->noContent();
    }
}
