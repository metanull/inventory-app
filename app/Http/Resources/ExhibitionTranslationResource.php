<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ExhibitionTranslationResource extends JsonResource
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
            // The exhibition this translation belongs to (ExhibitionResource id)
            'exhibition_id' => $this->exhibition_id,
            // The language of this translation (LanguageResource id)
            'language_id' => $this->language_id,
            // The context of this translation (ContextResource id)
            'context_id' => $this->context_id,
            // The title of the exhibition translation
            'title' => $this->title,
            // The description of the exhibition translation
            'description' => $this->description,
            // The URL for the exhibition translation
            'url' => $this->url,
            // The Id(s) of matching resource in the legacy system (if any).
            'backward_compatibility' => $this->backward_compatibility,
            // Extra data for translation (object, may be null)
            'extra' => $this->extra,
            // The date of creation of the resource (managed by the system)
            'created_at' => $this->created_at,
            // The date of last modification of the resource (managed by the system)
            'updated_at' => $this->updated_at,
        ];
    }
}
