<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Gallery Resource
 *
 * Transforms Gallery model data for API responses.
 * Includes relationships and computed attributes for API consumption.
 */
class GalleryResource extends JsonResource
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
            'backward_compatibility' => $this->backward_compatibility,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            // Relationships
            'translations' => GalleryTranslationResource::collection($this->whenLoaded('translations')),
            'partners' => PartnerResource::collection($this->whenLoaded('partners')),
            'items' => ItemResource::collection($this->whenLoaded('items')),
            'details' => DetailResource::collection($this->whenLoaded('details')),

            // Computed attributes
            'items_count' => $this->when($this->relationLoaded('items'), fn () => $this->items->count()),
            'details_count' => $this->when($this->relationLoaded('details'), fn () => $this->details->count()),
            'total_content_count' => $this->when(
                $this->relationLoaded('items') && $this->relationLoaded('details'),
                fn () => $this->items->count() + $this->details->count()
            ),
            'partners_count' => $this->when($this->relationLoaded('partners'), fn () => $this->partners->count()),
            'translations_count' => $this->when($this->relationLoaded('translations'), fn () => $this->translations->count()),
        ];
    }
}
