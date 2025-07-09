<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Collection Resource
 *
 * Transforms Collection model data for API responses.
 * Includes relationships and computed attributes for API consumption.
 */
class CollectionResource extends JsonResource
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
            'internal_name' => $this->internal_name,
            'language_id' => $this->language_id,
            'context_id' => $this->context_id,
            'backward_compatibility' => $this->backward_compatibility,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            // Relationships
            'language' => new LanguageResource($this->whenLoaded('language')),
            'context' => new ContextResource($this->whenLoaded('context')),
            'translations' => CollectionTranslationResource::collection($this->whenLoaded('translations')),
            'partners' => PartnerResource::collection($this->whenLoaded('partners')),
            'items' => ItemResource::collection($this->whenLoaded('items')),

            // Computed attributes
            'items_count' => $this->when($this->relationLoaded('items'), fn () => $this->items->count()),
            'partners_count' => $this->when($this->relationLoaded('partners'), fn () => $this->partners->count()),
            'translations_count' => $this->when($this->relationLoaded('translations'), fn () => $this->translations->count()),
        ];
    }
}
