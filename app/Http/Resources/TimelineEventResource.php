<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TimelineEventResource extends JsonResource
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
            'timeline_id' => $this->timeline_id,
            'internal_name' => $this->internal_name,
            'year_from' => $this->year_from,
            'year_to' => $this->year_to,
            'year_from_ah' => $this->year_from_ah,
            'year_to_ah' => $this->year_to_ah,
            'date_from' => $this->date_from,
            'date_to' => $this->date_to,
            'display_order' => $this->display_order,
            'backward_compatibility' => $this->backward_compatibility,
            'extra' => $this->extra,
            'timeline' => new TimelineResource($this->whenLoaded('timeline')),
            'translations' => TimelineEventTranslationResource::collection($this->whenLoaded('translations')),
            'images' => TimelineEventImageResource::collection($this->whenLoaded('images')),
            'items' => ItemResource::collection($this->whenLoaded('items')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
