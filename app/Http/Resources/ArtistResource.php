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
            'id' => $this->id,
            'name' => $this->name,
            'place_of_birth' => $this->place_of_birth,
            'place_of_death' => $this->place_of_death,
            'date_of_birth' => $this->date_of_birth,
            'date_of_death' => $this->date_of_death,
            'period_of_activity' => $this->period_of_activity,
            'internal_name' => $this->internal_name,
            'backward_compatibility' => $this->backward_compatibility,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'items' => ItemResource::collection($this->whenLoaded('items')),
        ];
    }
}
