<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArtistResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            // The unique identifier (GUID)
            'id' => $this->id,
            // The name of the artist
            'name' => $this->name,
            // The place of birth of the artist
            'place_of_birth' => $this->place_of_birth,
            // The place of death of the artist
            'place_of_death' => $this->place_of_death,
            // The date of birth of the artist
            'date_of_birth' => $this->date_of_birth,
            // The date of death of the artist
            'date_of_death' => $this->date_of_death,
            // The period of activity of the artist
            'period_of_activity' => $this->period_of_activity,
            // A name for this resource, for internal use only.
            'internal_name' => $this->internal_name,
            // The Id(s) of matching resource in the legacy system (if any).
            'backward_compatibility' => $this->backward_compatibility,
            // The date of creation of the resource (managed by the system)
            'created_at' => $this->created_at,
            // The date of last modification of the resource (managed by the system)
            'updated_at' => $this->updated_at,
            // Items associated with this artist (ItemResource[])
            'items' => ItemResource::collection($this->whenLoaded('items')),
        ];
    }
}
