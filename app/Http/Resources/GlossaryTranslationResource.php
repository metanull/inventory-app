<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GlossaryTranslationResource extends JsonResource
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
            // The glossary this translation belongs to
            'glossary_id' => $this->glossary_id,
            // The language of this translation
            'language_id' => $this->language_id,
            // The definition/translation text
            'definition' => $this->definition,
            // The date of creation of the resource (managed by the system)
            'created_at' => $this->created_at,
            // The date of last modification of the resource (managed by the system)
            'updated_at' => $this->updated_at,
            // Relationships (only included if loaded)
            'glossary' => new GlossaryResource($this->whenLoaded('glossary')),
            'language' => new LanguageResource($this->whenLoaded('language')),
        ];
    }
}
