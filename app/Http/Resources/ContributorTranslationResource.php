<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContributorTranslationResource extends JsonResource
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
            'contributor_id' => $this->contributor_id,
            'language_id' => $this->language_id,
            'context_id' => $this->context_id,
            'name' => $this->name,
            'description' => $this->description,
            'link' => $this->link,
            'alt_text' => $this->alt_text,
            'extra' => $this->extra,
            'backward_compatibility' => $this->backward_compatibility,
            // Relationships
            'contributor' => new ContributorResource($this->whenLoaded('contributor')),
            'language' => new LanguageResource($this->whenLoaded('language')),
            'context' => new ContextResource($this->whenLoaded('context')),
            // Timestamps
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
