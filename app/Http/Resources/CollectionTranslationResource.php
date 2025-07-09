<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * CollectionTranslation Resource
 *
 * Transforms CollectionTranslation model data for API responses.
 * Includes relationships and computed attributes for API consumption.
 */
class CollectionTranslationResource extends JsonResource
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
            'collection_id' => $this->collection_id,
            'language_id' => $this->language_id,
            'context_id' => $this->context_id,
            'title' => $this->title,
            'description' => $this->description,
            'url' => $this->url,
            'backward_compatibility' => $this->backward_compatibility,
            'extra' => $this->extra,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            // Relationships
            'collection' => new CollectionResource($this->whenLoaded('collection')),
            'language' => new LanguageResource($this->whenLoaded('language')),
            'context' => new ContextResource($this->whenLoaded('context')),
        ];
    }
}
