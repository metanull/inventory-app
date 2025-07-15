<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DetailResource extends JsonResource
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
            // The item this detail belongs to (ItemResource)
            'item' => new ItemResource($this->whenLoaded('item')),
            // The Id(s) of matching resource in the legacy system (if any).
            'backward_compatibility' => $this->backward_compatibility,
            // Translations for this detail (internationalization and contextualization) (DetailTranslationResource[])
            'translations' => DetailTranslationResource::collection($this->whenLoaded('translations')),
            // The date of creation of the resource (managed by the system)
            'created_at' => $this->created_at,
            // The date of last modification of the resource (managed by the system)
            'updated_at' => $this->updated_at,
        ];
    }
}
