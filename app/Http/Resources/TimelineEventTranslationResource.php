<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TimelineEventTranslationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'timeline_event_id' => $this->timeline_event_id,
            'language_id' => $this->language_id,
            'name' => $this->name,
            'description' => $this->description,
            'date_from_description' => $this->date_from_description,
            'date_to_description' => $this->date_to_description,
            'date_from_ah_description' => $this->date_from_ah_description,
            'timeline_event' => new TimelineEventResource($this->whenLoaded('timelineEvent')),
            'language' => new LanguageResource($this->whenLoaded('language')),
            'backward_compatibility' => $this->backward_compatibility,
            'extra' => $this->extra,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
