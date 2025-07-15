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
            // The unique identifier (GUID)
            'id' => $this->id,
            // A name for this resource, for internal use only.
            'internal_name' => $this->internal_name,
            // The Id(s) of matching resource in the legacy system (if any).
            'backward_compatibility' => $this->backward_compatibility,
            // The date of creation of the resource (managed by the system)
            'created_at' => $this->created_at,
            // The date of last modification of the resource (managed by the system)
            'updated_at' => $this->updated_at,

            // Translations for this gallery (GalleryTranslationResource[])
            'translations' => GalleryTranslationResource::collection($this->whenLoaded('translations')),
            // Partners associated with this gallery (PartnerResource[])
            'partners' => PartnerResource::collection($this->whenLoaded('partners')),
            // Items associated with this gallery (ItemResource[])
            'items' => ItemResource::collection($this->whenLoaded('items')),
            // Details associated with this gallery (DetailResource[])
            'details' => DetailResource::collection($this->whenLoaded('details')),

            // The number of items in this gallery (computed)
            'items_count' => $this->when($this->relationLoaded('items'), fn () => $this->items->count()),
            // The number of details in this gallery (computed)
            'details_count' => $this->when($this->relationLoaded('details'), fn () => $this->details->count()),
            // The total number of content items in this gallery (computed)
            'total_content_count' => $this->when(
                $this->relationLoaded('items') && $this->relationLoaded('details'),
                fn () => $this->items->count() + $this->details->count()
            ),
            // The total number of partners in this gallery (computed)
            'partners_count' => $this->when($this->relationLoaded('partners'), fn () => $this->partners->count()),
            // The total number of translations in this gallery (computed)
            'translations_count' => $this->when($this->relationLoaded('translations'), fn () => $this->translations->count()),
        ];
    }
}
