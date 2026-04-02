<?php

namespace App\Events;

use App\Models\TimelineEventTranslation;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TimelineEventTranslationSaved
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public readonly TimelineEventTranslation $timelineEventTranslation
    ) {}
}
