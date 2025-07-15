<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Province resource for API responses.
 */
class ProvinceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
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
            // The country this province belongs to (CountryResource id)
            'country_id' => $this->country_id,
            // Translations for this province (ProvinceTranslationResource[])
            'translations' => $this->whenLoaded('translations', function () {
                return ProvinceTranslationResource::collection($this->translations);
            }),
            // The date of creation of the resource (managed by the system)
            'created_at' => $this->created_at,
            // The date of last modification of the resource (managed by the system)
            'updated_at' => $this->updated_at,
        ];
    }
}
