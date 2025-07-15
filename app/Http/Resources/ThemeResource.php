<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ThemeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            // The unique identifier (GUID)
            'id' => $this->id,
            // The exhibition this theme belongs to (ExhibitionResource id)
            'exhibition_id' => $this->exhibition_id,
            // The parent theme of this theme (ThemeResource id)
            'parent_id' => $this->parent_id,
            // A name for this resource, for internal use only.
            'internal_name' => $this->internal_name,
            // The Id(s) of matching resource in the legacy system (if any).
            'backward_compatibility' => $this->backward_compatibility,
            // Translations for this theme (ThemeTranslationResource[])
            'translations' => ThemeTranslationResource::collection($this->whenLoaded('translations')),
            // Subthemes of this theme (ThemeResource[])
            'subthemes' => ThemeResource::collection($this->whenLoaded('subthemes')),
            // The date of creation of the resource (managed by the system)
            'created_at' => $this->created_at,
            // The date of last modification of the resource (managed by the system)
            'updated_at' => $this->updated_at,
        ];
    }
}
