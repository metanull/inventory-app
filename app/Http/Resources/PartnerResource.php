<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PartnerResource extends JsonResource
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
            // A name for this resource, for internal use only.
            'internal_name' => $this->internal_name,
            // The Id(s) of matching resource in the legacy system (if any).
            'backward_compatibility' => $this->backward_compatibility,
            // The type of the partner, either 'museum', 'institution' or 'individual'.
            'type' => $this->type,
            // The country this partner is associated with, nullable (CountryResource)
            'country' => new CountryResource($this->whenLoaded('country')),
            // GPS Location
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'map_zoom' => $this->map_zoom,
            // Relationships
            'project' => new ProjectResource($this->whenLoaded('project')),
            'monument_item' => new ItemResource($this->whenLoaded('monumentItem')),
            'translations' => PartnerTranslationResource::collection($this->whenLoaded('translations')),
            'images' => PartnerImageResource::collection($this->whenLoaded('partnerImages')),
            'collections' => CollectionResource::collection($this->whenLoaded('collections')),
            // Visibility
            'visible' => $this->visible,
            // The date of creation of the resource (managed by the system)
            'created_at' => $this->created_at,
            // The date of last modification of the resource (managed by the system)
            'updated_at' => $this->updated_at,
        ];
    }
}
