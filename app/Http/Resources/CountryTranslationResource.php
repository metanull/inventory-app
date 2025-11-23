<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CountryTranslationResource extends JsonResource
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
            'country_id' => $this->country_id,
            'language_id' => $this->language_id,
            'name' => $this->name,
            // Relationships
            'country' => new CountryResource($this->whenLoaded('country')),
            'language' => new LanguageResource($this->whenLoaded('language')),
            // Metadata
            'backward_compatibility' => $this->backward_compatibility,
            'extra' => $this->extra,
            // Timestamps
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
