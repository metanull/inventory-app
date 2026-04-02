<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TimelineResource extends JsonResource
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
            'internal_name' => $this->internal_name,
            'country_id' => $this->country_id,
            'collection_id' => $this->collection_id,
            'backward_compatibility' => $this->backward_compatibility,
            'extra' => $this->extra,
            'country' => new CountryResource($this->whenLoaded('country')),
            'collection' => new CollectionResource($this->whenLoaded('collection')),
            'events' => TimelineEventResource::collection($this->whenLoaded('events')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
