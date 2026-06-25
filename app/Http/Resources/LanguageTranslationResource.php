<?php

namespace App\Http\Resources;

use App\Models\LanguageTranslation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin LanguageTranslation */
class LanguageTranslationResource extends JsonResource
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
            'language_id' => $this->language_id,
            'display_language_id' => $this->display_language_id,
            'name' => $this->name,
            // Relationships
            'language' => new LanguageResource($this->whenLoaded('language')),
            'display_language' => new LanguageResource($this->whenLoaded('displayLanguage')),
            // Metadata
            'backward_compatibility' => $this->backward_compatibility,
            'extra' => $this->extra,
            // Timestamps
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
